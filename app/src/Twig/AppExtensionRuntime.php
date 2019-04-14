<?php
/**
 * @file AppExtensionRuntime.php
 */

namespace App\Twig;


use App\Entity\ContestType;
use App\Entity\ItemInVersionGroup;
use App\Entity\LocationMap;
use App\Entity\Pokemon;
use App\Entity\PokemonType;
use App\Entity\Type;
use App\Entity\TypeEfficacy;
use App\Entity\Version;
use App\Helpers\Labeler;
use App\Mechanic\TypeMatchup;
use App\Repository\TypeChartRepository;
use App\Repository\VersionRepository;
use Twig\Environment;
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
     * @param \Twig_Environment $twig
     * @param array $context
     * @param Type $attackingType
     *
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function damageChartAttacking(\Twig_Environment $twig, array $context, Type $attackingType): string
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
     * @param \Twig_Environment $twig
     * @param array $context
     * @param PokemonType|Type|PokemonType[]|Type[] $defendingType
     *
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function damageChartDefending(\Twig_Environment $twig, array $context, $defendingType): string
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
     * @param \Twig_Environment $twig
     * @param array $context
     * @param Type|ContestType $value
     *
     * @param bool $link
     *
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function typeEmblem(\Twig_Environment $twig, array $context, $value, bool $link = true): string
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
     * @param \Twig_Environment $twig
     * @param TypeEfficacy|int $value
     *
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function typeEfficacy(\Twig_Environment $twig, $value): string
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
     * @param LocationMap $map
     *
     * @return string
     */
    public function locationMap(Environment $twig, ?LocationMap $map): string
    {
        if ($map === null) {
            return '';
        }

        $mapImageUrl = $this->projectDir.'/assets/static/map/'.$map->getMap()->getUrl();
        $mapImageInfo = getimagesize($mapImageUrl);
        $imageWidth = $mapImageInfo[0];
        $imageHeight = $mapImageInfo[1];

        return $twig->render(
            '_filters/map.svg.twig',
            [
                'map' => $map,
                'width' => $imageWidth,
                'height' => $imageHeight,
            ]
        );
    }
}
