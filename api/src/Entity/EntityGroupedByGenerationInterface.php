<?php


namespace App\Entity;

/**
 * Entities groupable by Generation.
 */
interface EntityGroupedByGenerationInterface extends EntityGroupedInterface
{

    /**
     * @return Generation
     */
    public function getGeneration(): ?Generation;

    /**
     * @param Generation $generation
     *
     * @return self
     */
    public function setGeneration(?Generation $generation);

    /**
     * {@inheritdoc}
     * @return Generation
     */
    public function getGroup(): GroupableInterface;
}
