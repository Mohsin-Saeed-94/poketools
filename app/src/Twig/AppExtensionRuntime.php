<?php
/**
 * @file AppExtensionRuntime.php
 */

namespace App\Twig;


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
}
