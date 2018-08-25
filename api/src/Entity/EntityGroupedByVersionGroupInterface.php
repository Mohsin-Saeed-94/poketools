<?php


namespace App\Entity;

/**
 * Entities groupable by Version Group.
 */
interface EntityGroupedByVersionGroupInterface
{

    /**
     * @return VersionGroup
     */
    public function getVersionGroup(): ?VersionGroup;

    /**
     * @param VersionGroup $versionGroup
     *
     * @return self
     */
    public function setVersionGroup(?VersionGroup $versionGroup);
}
