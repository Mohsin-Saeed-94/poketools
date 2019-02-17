<?php


namespace App\Entity;

/**
 * Entities groupable by Version.
 */
interface EntityGroupedByVersionInterface extends EntityGroupedInterface
{

    /**
     * @return Version
     */
    public function getVersion(): Version;

    /**
     * @param Version $version
     *
     * @return self
     */
    public function setVersion(Version $version);

    /**
     * {@inheritdoc}
     * @return Version
     */
    public function getGroup(): GroupableInterface;
}
