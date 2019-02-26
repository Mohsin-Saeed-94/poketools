<?php
/**
 * @file AppExtensionRuntime.php
 */

namespace App\Twig;


use App\Entity\ContestType;
use App\Entity\Type;
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
    private $versionRepository;

    /**
     * AppExtensionRuntime constructor.
     *
     * @param VersionRepository $versionRepository
     */
    public function __construct(VersionRepository $versionRepository)
    {
        $this->versionRepository = $versionRepository;
    }

    /**
     * Get a list of all versions
     *
     * @return \App\Entity\Version[]
     */
    public function versionList()
    {
        return $this->versionRepository->findAllVersionsGroupedByGeneration();
    }

    /**
     * @param \Twig_Environment $twig
     * @param Type|ContestType $value
     *
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function typeEmblem(\Twig_Environment $twig, $value)
    {
        if (!$value instanceof Type && !$value instanceof ContestType) {
            $type = is_object($value) ? get_class($value) : gettype($value);
            throw new \InvalidArgumentException('Argument to type must be a Type or ContestType, got '.$type);
        }

        return $twig->render('_filters/type_emblem.twig', ['value' => $value]);
    }
}
