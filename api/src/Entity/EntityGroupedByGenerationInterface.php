<?php


namespace App\Entity;

/**
 * Entities groupable by Generation.
 */
interface EntityGroupedByGenerationInterface
{

    /**
     * @return Generation
     */
    public function getGeneration(): Generation;

    /**
     * @param Generation $generation
     *
     * @return self
     */
    public function setGeneration(Generation $generation);
}
