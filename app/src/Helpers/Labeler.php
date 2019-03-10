<?php
/**
 * @file Labeler.php
 */

namespace App\Helpers;

use App\Entity\ItemInVersionGroup;
use App\Entity\Pokemon;
use App\Entity\Version;
use App\Repository\VersionRepository;

/**
 * Generate labels for entities
 */
final class Labeler
{
    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * @var Version|null
     */
    protected $version;

    /**
     * @var VersionRepository
     */
    protected $versionRepo;

    /**
     * Labeler constructor.
     *
     * @param \Twig_Environment $twig
     * @param Version $activeVersion
     * @param VersionRepository $versionRepo
     */
    public function __construct(\Twig_Environment $twig, ?Version $activeVersion, VersionRepository $versionRepo)
    {
        $this->twig = $twig;
        $this->version = $activeVersion;
    }

    /**
     * @param ItemInVersionGroup $item
     * @param Version|null $version
     *
     * @return string
     */
    public function item(ItemInVersionGroup $item, Version $version = null)
    {
        $version = $this->resolveCurrentVersion($version);

        return $this->twig->render(
            '_label/item.html.twig',
            [
                'version' => $version,
                'item' => $item,
            ]
        );
    }

    /**
     * @param Version|null $selectedVersion
     *
     * @return Version
     */
    protected function resolveCurrentVersion(?Version $selectedVersion): Version
    {
        if ($selectedVersion !== null) {
            return $selectedVersion;
        }

        if ($this->version !== null) {
            return $this->version;
        }

        return $this->versionRepo->getDefaultVersion();
    }

    /**
     * @param Pokemon $pokemon
     * @param Version|null $version
     *
     * @return string
     */
    public function pokemon(Pokemon $pokemon, Version $version = null)
    {
        $version = $this->resolveCurrentVersion($version);

        return $this->twig->render(
            '_label/pokemon.html.twig',
            [
                'version' => $version,
                'pokemon' => $pokemon,
            ]
        );
    }
}