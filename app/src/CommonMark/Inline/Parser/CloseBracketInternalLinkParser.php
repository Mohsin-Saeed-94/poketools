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
use App\Entity\TypeChart;
use App\Entity\Version;
use App\Repository\SlugAndVersionInterface;
use Doctrine\ORM\EntityManagerInterface;
use League\CommonMark\Cursor;
use League\CommonMark\Inline\Element\Link;
use League\CommonMark\Inline\Element\Text;
use League\CommonMark\Inline\Parser\CloseBracketParser;
use League\CommonMark\InlineParserContext;
use League\CommonMark\Util\RegexHelper;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Extends the stock link parser to include support for internal links.
 *
 * Links should be in the format `[optional label]{category:slug}`.
 */
class CloseBracketInternalLinkParser extends CloseBracketParser
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
    public function parse(InlineParserContext $inlineContext)
    {
        // see createInline for the odd reasons behind this.
        $this->currentLink = null;
        $this->currentEntity = null;

        if (parent::parse($inlineContext) === false) {
            return false;
        }

        if ($this->currentLink->firstChild() === null) {
            // The link has no text.
            $label = new Text($this->currentEntity->getName());
            $this->currentLink->appendChild($label);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function createInline($url, $title, $isImage)
    {
        // Capture the link when it is created so it can be further modified.
        // This is the only hook into the parent parsing process.
        $this->currentLink = parent::createInline($url, $title, $isImage);

        return $this->currentLink;
    }

    /**
     * {@inheritdoc}
     */
    protected function tryParseInlineLinkAndTitle(Cursor $cursor)
    {
        if ($cursor->getCharacter() === self::BRACKET_OPEN) {
            return $this->tryParseInternalLink($cursor);
        }

        return parent::tryParseInlineLinkAndTitle($cursor);
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
            $this->logger->warning(sprintf('Could not find destination for internal link: "%s".', $ref));
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

    protected function getUri(string $category, string $slug): ?string
    {
        // Make sure version is available if necessary.
        $requiresVersion = [
            'ability',
            'item',
            'location',
            'move',
            'nature',
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

}
