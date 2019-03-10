<?php
/**
 * @file AppExtensionRuntime.php
 */

namespace App\Twig;


use App\Entity\ContestType;
use App\Entity\ItemInVersionGroup;
use App\Entity\Type;
use App\Entity\TypeEfficacy;
use App\Entity\Version;
use App\Helpers\Labeler;
use App\Repository\TypeChartRepository;
use App\Repository\VersionRepository;
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
     * @var Version
     */
    private $activeVersion;

    /**
     * @var Labeler
     */
    private $labeler;

    /**
     * AppExtensionRuntime constructor.
     *
     * @param VersionRepository $versionRepo
     * @param TypeChartRepository $typeChartRepo
     * @param Version $activeVersion
     * @param Labeler $labeler
     */
    public function __construct(
        VersionRepository $versionRepo,
        TypeChartRepository $typeChartRepo,
        ?Version $activeVersion,
        Labeler $labeler
    ) {
        $this->versionRepo = $versionRepo;
        $this->typeChartRepo = $typeChartRepo;
        $this->activeVersion = $activeVersion;
        $this->labeler = $labeler;
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
     * @param Type $value
     *
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function damageChartAttacking(\Twig_Environment $twig, array $context, Type $value): string
    {
        $version = $this->resolveActiveVersion($context);
        $typeChart = $this->typeChartRepo->findOneByVersion($version);

        return $twig->render(
            '_functions/damage_chart_attacking.html.twig',
            [
                'version' => $version,
                'attacking_type' => $value,
                'type_chart' => $typeChart,
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
     * @param Type $value
     *
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function damageChartDefending(\Twig_Environment $twig, array $context, Type $value): string
    {
        $version = $this->resolveActiveVersion($context);
        $typeChart = $this->typeChartRepo->findOneByVersion($version);

        return $twig->render(
            '_functions/damage_chart_defending.html.twig',
            [
                'version' => $version,
                'defending_type' => $value,
                'type_chart' => $typeChart,
            ]
        );
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
     * @param TypeEfficacy $value
     *
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function typeEfficacy(\Twig_Environment $twig, TypeEfficacy $value): string
    {
        switch ($value->getEfficacy()) {
            case 25:
                $formatted = '&frac14;';
                break;
            case 50:
                $formatted = '&frac12;';
                break;
            default:
                $formatted = (string)($value->getEfficacy() / 100);
        }

        return $twig->render('_filters/type_efficacy.twig', ['value' => $value, 'formatted' => $formatted]);
    }

    /**
     * @param array $context
     * @param ItemInVersionGroup $item
     *
     * @return string
     */
    public function itemLabel(array $context, ItemInVersionGroup $item): string
    {
        $version = $this->resolveActiveVersion($context);

        return $this->labeler->item($item, $version);
    }
}
