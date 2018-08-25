<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass="App\Repository\SuperContestEffectRepository")
 */
class SuperContestEffect extends AbstractDexEntity implements EntityHasFlavorTextInterface, EntityHasDescriptionInterface
{

    use EntityHasFlavorTextTrait;
    use EntityHasDescriptionTrait;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @Assert\NotNull()
     * @Assert\GreaterThanOrEqual(0)
     */
    protected $appeal;

    /**
     * @return int
     */
    public function getAppeal(): int
    {
        return $this->appeal;
    }

    /**
     * @param int $appeal
     *
     * @return self
     */
    public function setAppeal(int $appeal): self
    {
        $this->appeal = $appeal;

        return $this;
    }
}
