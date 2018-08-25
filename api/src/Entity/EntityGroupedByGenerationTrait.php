<?php


namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Default implementation of App\Entity\EntityGroupedByGenerationInterface.
 */
trait EntityGroupedByGenerationTrait
{

    /**
     * @var Generation
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Generation")
     * @Assert\NotNull()
     */
    protected $generation;

    /**
     * @return Generation
     */
    public function getGeneration(): Generation
    {
        return $this->generation;
    }

    /**
     * @param Generation $generation
     *
     * @return self
     */
    public function setGeneration(Generation $generation): self
    {
        $this->generation = $generation;

        return $this;
    }
}
