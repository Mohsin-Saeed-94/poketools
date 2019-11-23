<?php
/**
 * @file CloseBrackerWithInternalLinkParser.php
 */

namespace App\CommonMark\Inline\Parser;


use App\Entity\AbilityInVersionGroup;
use App\Entity\EntityHasNameInterface;
use App\Entity\ItemInVersionGroup;
use App\Entity\LocationInVersionGroup;
use App\Entity\MoveInVersionGroup;
use App\Entity\Pokemon;
use App\Entity\PokemonSpeciesInVersionGroup;
use App\Entity\TypeChart;
use App\Entity\Version;
use App\Repository\SlugAndVersionInterface;
use Doctrine\ORM\EntityManagerInterface;
use League\CommonMark\Cursor;
use League\CommonMark\EnvironmentAwareInterface;
use League\CommonMark\EnvironmentInterface;
use League\CommonMark\Inline\Element\AbstractWebResource;
use League\CommonMark\Inline\Element\Image;
use League\CommonMark\Inline\Element\Link;
use League\CommonMark\Inline\Element\Text;
use League\CommonMark\Inline\Parser\InlineParserInterface;
use League\CommonMark\InlineParserContext;
use League\CommonMark\Util\RegexHelper;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Extends the stock link parser to include support for internal links.
 *
 * Links should be in the format `[optional label]{category:slug}`.
 */
class CloseBracketInternalLinkParser implements InlineParserInterface, EnvironmentAwareInterface
{
    protected const BRACKET_OPEN = '{';

    protected const BRACKET_CLOSE = '}';
    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * @var UrlGeneratorInterface
     */
    protected $urlGenerator;
    /**
     * @var EntityManagerInterface
     */
    protected $em;
    /**
     * @var Version
     */
    protected $currentVersion;
    /**
     * Track the current link being worked with.
     *
     * @see CloseBracketInternalLinkParser::createInline()
     *
     * @var Link|null
     */
    protected $currentLink;
    /**
     * Current entity being worked with, guaranteed to have a getName() method.
     *
     * @var EntityHasNameInterface
     */
    protected $currentEntity;
    /**
     * @var EnvironmentInterface
     */
    private $environment;

    /**
     * CloseBracketInternalLinkParser constructor.
     *
     * @param LoggerInterface $logger
     * @param UrlGeneratorInterface $urlGenerator
     * @param EntityManagerInterface $em
     * @param Version $version
     */
    public function __construct(
        LoggerInterface $logger,
        UrlGeneratorInterface $urlGenerator,
        EntityManagerInterface $em,
        Version $version
    ) {
        $this->logger = $logger;
        $this->urlGenerator = $urlGenerator;
        $this->em = $em;
        $this->currentVersion = $version;
    }

    /**
     * {@inheritdoc}
     */
    public function parse(InlineParserContext $inlineContext): bool
    {
        // Look through stack of delimiters for a [ or !
        $opener = $inlineContext->getDelimiterStack()->searchByCharacter(['[', '!']);
        if ($opener === null) {
            return false;
        }

        if (!$opener->isActive()) {
            // no matched opener; remove from emphasis stack
            $inlineContext->getDelimiterStack()->removeDelimiter($opener);

            return false;
        }

        $cursor = $inlineContext->getCursor();

        $previousState = $cursor->saveState();
        $cursor->advance();

        // Check to see if we have a link/image
        $link = $this->tryParseInlineLinkAndTitle($cursor);
        if (!$link) {
            // No match
            $inlineContext->getDelimiterStack()->removeDelimiter($opener); // Remove this opener from stack
            $cursor->restoreState($previousState);

            return false;
        }

        $isImage = ($opener->getChar() === '!');

        $inline = $this->createInline($link['url'], $link['title'], $isImage);

        // Add link text if needed
        if (!$inline->firstChild()) {
            // The link has no text.
            $inline->appendChild(new Text($this->currentEntity->getName()));
        }

        $opener->getInlineNode()->replaceWith($inline);
        while (($label = $inline->next()) !== null) {
            $inline->appendChild($label);
        }

        // Process delimiters such as emphasis inside link/image
        $delimiterStack = $inlineContext->getDelimiterStack();
        $stackBottom = $opener->getPrevious();
        $delimiterStack->processDelimiters($stackBottom, $this->environment->getDelimiterProcessors());
        $delimiterStack->removeAll($stackBottom);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function tryParseInlineLinkAndTitle(Cursor $cursor)
    {
        if ($cursor->getCharacter() === self::BRACKET_OPEN) {
            return $this->tryParseInternalLink($cursor);
        }

        return false;
    }

    /**
     * @param Cursor $cursor
     *
     * @return array|bool
     */
    protected function tryParseInternalLink(Cursor $cursor)
    {
        $previousState = $cursor->saveState();

        // Parse URL
        $cursor->advance();
        $cursor->advanceToNextNonSpaceOrNewline();
        $dest = $this->parseInternalDestination($cursor);
        if ($dest === null) {
            $cursor->restoreState($previousState);

            return false;
        }
        $cursor->advance();

        // Don't support title text here.

        return ['url' => $dest, 'title' => null];
    }

    /**
     * @param Cursor $cursor
     *
     * @return string|null
     */
    protected function parseInternalDestination(Cursor $cursor): ?string
    {
        // Get the text between brackets pointing to the destination.
        $oldState = $cursor->saveState();
        $openParens = 0;
        while (($c = $cursor->getCharacter()) !== null) {
            if ($c === '\\' && RegexHelper::isEscapable($cursor->peek())) {
                $cursor->advanceBy(2);
            } elseif ($c === self::BRACKET_OPEN) {
                $cursor->advance();
                $openParens++;
            } elseif ($c === self::BRACKET_CLOSE) {
                if ($openParens < 1) {
                    break;
                }

                $cursor->advance();
                $openParens--;
            } elseif (preg_match(RegexHelper::REGEX_WHITESPACE_CHAR, $c)) {
                break;
            } else {
                $cursor->advance();
            }
        }
        $newPos = $cursor->getPosition();
        $cursor->restoreState($oldState);
        $cursor->advanceBy($newPos - $cursor->getPosition());

        // The destination reference (e.g. "mechanic:hp")
        $ref = $cursor->getPreviousText();
        $refParts = $this->getRefParts($ref);
        if ($refParts === null) {
            return null;
        }

        $uri = $this->getUri($refParts['category'], $refParts['slug']);
        if ($uri === null) {
            // Help with finding bad links in the data.
            $this->logger->error(sprintf('Could not find destination for internal link: "%s".', $ref));
        }

        return $uri;
    }

    /**
     * @param string $ref
     *
     * @return array|null
     */
    protected function getRefParts(string $ref): ?array
    {
        $badChars = ':'.self::BRACKET_OPEN.self::BRACKET_CLOSE;
        if (!preg_match("/^(?P<category>[^${badChars}]+):(?P<slug>[^${badChars}]+)$/", $ref, $matches)) {
            return null;
        }

        return $matches;
    }

    /**
     * Get the URI for the link.
     *
     * @param string $category
     * @param string $slug
     * @return string|null
     */
    protected function getUri(string $category, string $slug): ?string
    {
        // Make sure version is available if necessary.
        $requiresVersion = [
            'ability',
            'item',
            'location',
            'move',
            'nature',
            'pokemon',
            'type',
        ];
        if ($this->currentVersion === null && in_array($category, $requiresVersion, true)) {
            return null;
        }

        switch ($category) {
            case 'mechanic':
                return $this->getMechanicLink($slug);
            case 'ability':
                if ($this->getEntityForLink(AbilityInVersionGroup::class, $slug, $this->currentVersion) === null) {
                    return $this->noEntityFound();
                }

                return $this->urlGenerator->generate(
                    'ability_view',
                    [
                        'versionSlug' => $this->currentVersion->getSlug(),
                        'abilitySlug' => $slug,
                    ]
                );
            case 'item':
                if ($this->getEntityForLink(ItemInVersionGroup::class, $slug, $this->currentVersion) === null) {
                    return $this->noEntityFound();
                }

                return $this->urlGenerator->generate(
                    'item_view',
                    [
                        'versionSlug' => $this->currentVersion->getSlug(),
                        'itemSlug' => $slug,
                    ]
                );
            case 'location':
                if ($this->getEntityForLink(LocationInVersionGroup::class, $slug, $this->currentVersion) === null) {
                    return $this->noEntityFound();
                }

                return $this->urlGenerator->generate(
                    'location_view',
                    [
                        'versionSlug' => $this->currentVersion->getSlug(),
                        'locationSlug' => $slug,
                    ]
                );
            case 'move':
                if ($this->getEntityForLink(MoveInVersionGroup::class, $slug, $this->currentVersion) === null) {
                    return $this->noEntityFound();
                }

                return $this->urlGenerator->generate(
                    'move_view',
                    [
                        'versionSlug' => $this->currentVersion->getSlug(),
                        'moveSlug' => $slug,
                    ]
                );
            case 'nature':
                if ($this->getEntityForLink(AbilityInVersionGroup::class, $slug, $this->currentVersion) === null) {
                    return $this->noEntityFound();
                }

                return $this->urlGenerator->generate(
                    'nature_view',
                    [
                        'versionSlug' => $this->currentVersion->getSlug(),
                        'natureSlug' => $slug,
                    ]
                );
            case 'pokemon':
                $slugParts = explode('/', $slug);
                /** @var PokemonSpeciesInVersionGroup|null $species */
                $species = $this->getEntityForLink(
                    PokemonSpeciesInVersionGroup::class,
                    $slugParts[0],
                    $this->currentVersion
                );
                if ($species === null) {
                    return $this->noEntityFound();
                }

                $params = [
                    'versionSlug' => $this->currentVersion->getSlug(),
                    'speciesSlug' => $slugParts[0],
                ];
                if (isset($slugParts[1])) {
                    // A Pokemon has been specified.
                    $pokemonRepo = $this->em->getRepository(Pokemon::class);
                    $pokemon = $pokemonRepo->findOneBySpecies($species, $this->currentVersion, $slugParts[1]);
                    if ($pokemon === null) {
                        return $this->noEntityFound();
                    }
                    $params['pokemonSlug'] = $slugParts[1];
                }

                return $this->urlGenerator->generate('pokemon_view', $params);
            case 'type':
                $typeChartRepo = $this->em->getRepository(TypeChart::class);
                $type = $typeChartRepo->findTypeInTypeChart($slug, $this->currentVersion);
                if ($type === null) {
                    return $this->noEntityFound();
                }
                $this->currentEntity = $type;

                return $this->urlGenerator->generate(
                    'type_view',
                    [
                        'versionSlug' => $this->currentVersion->getSlug(),
                        'typeSlug' => $slug,
                    ]
                );
        }

        return null;
    }

    /**
     * Get a link to Bulbapedia.
     *
     * @param string $slug
     *
     * @return string
     */
    protected function getMechanicLink(string $slug): string
    {
        $slug = rawurlencode($slug);

        return sprintf('https://bulbapedia.bulbagarden.net/wiki/Special:Search/%s', $slug);
    }

    /**
     * Get an entity by slug and version.
     *
     * This will also set the internal entity state variable.
     *
     * @param string $type
     *   An entity type whose repository implements App\Repository\SlugAndVersionInterface.
     * @param string $slug
     * @param Version $version
     *
     * @return object|null
     */
    protected function getEntityForLink(string $type, string $slug, Version $version): ?object
    {
        /** @var SlugAndVersionInterface $repo */
        $repo = $this->em->getRepository($type);
        $entity = $repo->findOneByVersion($slug, $version);
        if ($entity === null) {
            return null;
        }
        $this->currentEntity = $entity;

        return $entity;
    }

    /**
     * @return null
     */
    protected function noEntityFound()
    {
        $this->currentEntity = null;

        return null;
    }

    /**
     * @param string $url
     * @param string|null $title
     * @param bool $isImage
     *
     * @return AbstractWebResource
     */
    private function createInline(string $url, ?string $title, bool $isImage): AbstractWebResource
    {
        if ($isImage) {
            return new Image($url, null, $title);
        }

        return new Link($url, null, $title);
    }

    /**
     * @inheritDoc
     */
    public function getCharacters(): array
    {
        return [']'];
    }

    /**
     * @inheritDoc
     */
    public function setEnvironment(EnvironmentInterface $environment)
    {
        $this->environment = $environment;
    }
}
