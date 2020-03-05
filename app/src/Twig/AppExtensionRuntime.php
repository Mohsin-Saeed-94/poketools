<?php
/**
 * @file AppExtensionRuntime.php
 */

namespace App\Twig;


use App\Entity\AbilityInVersionGroup;
use App\Entity\ContestType;
use App\Entity\ItemInVersionGroup;
use App\Entity\LocationInVersionGroup;
use App\Entity\LocationMap;
use App\Entity\MoveInVersionGroup;
use App\Entity\Nature;
use App\Entity\Pokemon;
use App\Entity\PokemonType;
use App\Entity\Type;
use App\Entity\TypeEfficacy;
use App\Entity\Version;
use App\Entity\VersionGroup;
use App\Helpers\Labeler;
use App\Mechanic\TypeMatchup;
use App\Repository\TypeChartRepository;
use App\Repository\VersionRepository;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Extension\RuntimeExtensionInterface;

/**
 * Class AppExtensionRuntime
 *
 * Runtime for \App\Twig\AppExtension
 */
class AppExtensionRuntime implements RuntimeExtensionInterface
{
    /**
     * @var VersionRepository
     */
    private $versionRepo;

    /**
     * @var TypeChartRepository
     */
    private $typeChartRepo;

    /**
     * @var TypeMatchup
     */
    private $typeMatchup;

    /**
     * @var Version
     */
    private $activeVersion;

    /**
     * @var Labeler
     */
    private $labeler;

    /**
     * @var string
     */
    private $projectDir;

    /**
     * AppExtensionRuntime constructor.
     *
     * @param VersionRepository $versionRepo
     * @param TypeChartRepository $typeChartRepo
     * @param TypeMatchup $typeMatchup
     * @param Version $activeVersion
     * @param Labeler $labeler
     * @param string $projectDir
     */
    public function __construct(
        VersionRepository $versionRepo,
        TypeChartRepository $typeChartRepo,
        TypeMatchup $typeMatchup,
        ?Version $activeVersion,
        Labeler $labeler,
        string $projectDir
    ) {
        $this->versionRepo = $versionRepo;
        $this->typeChartRepo = $typeChartRepo;
        $this->typeMatchup = $typeMatchup;
        $this->activeVersion = $activeVersion;
        $this->labeler = $labeler;
        $this->projectDir = $projectDir;
    }

    /**
     * Get a list of all versions
     *
     * @return \App\Entity\Version[]
     */
    public function versionList(): array
    {
        return $this->versionRepo->findAllVersionsGroupedByGeneration();
    }

    /**
     * Decide which version to use in the version group.
     *
     * Prioritises the current version if it is part of the given version group,
     * otherwise uses the first version in the version group.
     *
     * @param array $context
     * @param VersionGroup $versionGroup
     *
     * @return Version
     */
    public function useVersion(array $context, VersionGroup $versionGroup): Version
    {
        $version = $context['version'] ?? $this->activeVersion;
        if ($version !== null && $versionGroup->getVersions()->contains($version)) {
            return $version;
        }

        return $versionGroup->getVersions()->first();
    }

    /**
     * @param Environment $twig
     * @param array $context
     * @param Type $attackingType
     *
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function damageChartAttacking(Environment $twig, array $context, Type $attackingType): string
    {
        $version = $this->resolveActiveVersion($context);
        $typeChart = $this->typeChartRepo->findOneByVersion($version);

        $efficacies = [];
        foreach ($typeChart->getTypes() as $defendingType) {
            $efficacies[$defendingType->getSlug()] = $this->typeMatchup->efficacy(
                $attackingType,
                $defendingType,
                $typeChart
            );
        }

        return $twig->render(
            '_functions/damage_chart_attacking.html.twig',
            [
                'version' => $version,
                'attacking_type' => $attackingType,
                'type_chart' => $typeChart,
                'efficacies' => $efficacies,
            ]
        );
    }

    /**
     * @param array $context
     *
     * @return Version|mixed|null
     */
    protected function resolveActiveVersion(array $context)
    {
        if (isset($context['version'])) {
            $version = $context['version'];
        } elseif (isset($this->activeVersion)) {
            $version = $this->activeVersion;
        } else {
            $version = $this->versionRepo->getDefaultVersion();
        }

        return $version;
    }

    /**
     * @param Environment $twig
     * @param array $context
     * @param PokemonType|Type|PokemonType[]|Type[] $defendingType
     *
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function damageChartDefending(Environment $twig, array $context, $defendingType): string
    {
        $version = $this->resolveActiveVersion($context);
        $typeChart = $this->typeChartRepo->findOneByVersion($version);
        $defendingType = $this->resolveTypes($defendingType);

        $efficacies = [];
        foreach ($typeChart->getTypes() as $attackingType) {
            $efficacies[$attackingType->getSlug()] = $this->typeMatchup->efficacy(
                $attackingType,
                $defendingType,
                $typeChart
            );
        }

        return $twig->render(
            '_functions/damage_chart_defending.html.twig',
            [
                'version' => $version,
                'defending_type' => $defendingType,
                'type_chart' => $typeChart,
                'efficacies' => $efficacies,
            ]
        );
    }

    /**
     * @param PokemonType|Type|PokemonType[]|Type[] $types
     *
     * @return PokemonType|PokemonType[]|Type|Type[]|array
     */
    protected function resolveTypes($types)
    {
        if (!is_iterable($types)) {
            $types = [$types];
        }

        $resolved = [];
        foreach ($types as $type) {
            if ($type instanceof PokemonType) {
                $resolved[] = $type->getType();
            } else {
                $resolved[] = $type;
            }
        }

        return $resolved;
    }

    /**
     * @param Environment $twig
     * @param array $context
     * @param Type|ContestType $value
     *
     * @param bool $link
     *
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function typeEmblem(Environment $twig, array $context, $value, bool $link = true): string
    {
        if (!$value instanceof Type && !$value instanceof ContestType) {
            $type = is_object($value) ? get_class($value) : gettype($value);
            throw new \InvalidArgumentException('Argument to type must be a Type or ContestType, got '.$type);
        }

        $version = $this->resolveActiveVersion($context);

        return $twig->render(
            '_filters/type_emblem.twig',
            [
                'value' => $value,
                'version' => $version,
                'link' => $link,
            ]
        );
    }

    /**
     * @param Environment $twig
     * @param TypeEfficacy|int $value
     *
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function typeEfficacy(Environment $twig, $value): string
    {
        if ($value instanceof TypeEfficacy) {
            $value = $value->getEfficacy();
        }
        switch ($value) {
            case 25:
                $formatted = '&frac14;';
                break;
            case 50:
                $formatted = '&frac12;';
                break;
            default:
                $formatted = (string)($value / 100);
        }

        return $twig->render('_filters/type_efficacy.twig', ['value' => $value, 'formatted' => $formatted]);
    }

    /**
     * @param array $context
     * @param ItemInVersionGroup $item
     *
     * @return string
     */
    public function labelItem(array $context, ItemInVersionGroup $item): string
    {
        $version = $this->resolveActiveVersion($context);

        return $this->labeler->item($item, $version);
    }

    /**
     * @param array $context
     * @param Pokemon $pokemon
     *
     * @return string
     */
    public function labelPokemon(array $context, Pokemon $pokemon): string
    {
        $version = $this->resolveActiveVersion($context);

        return $this->labeler->pokemon($pokemon, $version);
    }

    /**
     * @param Environment $twig
     * @param array $context
     * @param LocationMap[]|LocationMap|null $locationMaps
     *   A list of LocationMap entities
     * @param bool $link
     *   Should the highlighted overlay link to the location?  Defaults to false.
     *
     * @return string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function locationMap(Environment $twig, array $context, $locationMaps, bool $link = false): string
    {
        if ($locationMaps === null) {
            return '';
        }

        if (!is_array($locationMaps) && $locationMaps instanceof LocationMap) {
            $locationMaps = [$locationMaps];
        } elseif (!is_array($locationMaps)) {
            throw new RuntimeError('Invalid entity passed to locationMap');
        }

        // Can't link without version
        $version = $context['version'] ?? null;
        if ($link === true && !isset($version)) {
            trigger_error('Attempted to link to location in locationMap without Version available.', E_USER_WARNING);
            $link = false;
        }

        // Sanity check that all passed maps refer to the same image
        $primaryMap = $locationMaps[array_key_first($locationMaps)];
        $mapImageUrl = $this->projectDir.'/assets/static/map/'.$primaryMap->getMap()->getUrl();
        foreach ($locationMaps as $locationMap) {
            if ($locationMap->getMap() !== $primaryMap->getMap()) {
                throw new RuntimeError('The list of location maps do not all refer to the same image.');
            }
        }
        [$imageWidth, $imageHeight] = getimagesize($mapImageUrl);

        return $twig->render(
            '_filters/map.svg.twig',
            [
                'version' => $version,
                'primary_map' => $primaryMap,
                'maps' => $locationMaps,
                'width' => $imageWidth,
                'height' => $imageHeight,
                'link' => $link,
            ]
        );
    }

    /**
     * Render a search result.
     *
     * @param Environment $twig
     * @param array $context
     * @param array $result
     *
     * @return string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function searchResultTeaser(Environment $twig, array $context, array $result): string
    {
        ['entity' => $entity, 'elastica' => $elastica] = $result;
        $entityTemplates = [
            Pokemon::class => 'pokemon/teaser.html.twig',
            MoveInVersionGroup::class => 'move/teaser.html.twig',
            Type::class => 'type/teaser.html.twig',
            ItemInVersionGroup::class => 'item/teaser.html.twig',
            LocationInVersionGroup::class => 'location/teaser.html.twig',
            Nature::class => 'nature/teaser.html.twig',
            AbilityInVersionGroup::class => 'ability/teaser.html.twig',
        ];

        $templateArgs = [
            'entity' => $entity,
            'version' => $context['version'] ?? $this->activeVersion,
            'search_meta' => $elastica,
        ];

        // Must allow that the actual entity class may be different because of proxy objects.
        foreach ($entityTemplates as $entityClass => $entityTemplate) {
            if (is_a($entity, $entityClass)) {
                return $twig->render($entityTemplate, $templateArgs);
            }
        }

        // Default teaser, is an empty string.
        return '';
    }
}
