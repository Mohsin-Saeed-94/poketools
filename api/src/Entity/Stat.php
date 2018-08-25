<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;


/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass="App\Repository\StatRepository")
 */
class Stat extends AbstractDexEntity implements EntityHasNameInterface, EntityHasSlugInterface, EntityIsSortableInterface
{

    use EntityHasNameAndSlugTrait;
    use EntityIsSortableTrait;

    /**
     * @var MoveDamageClass
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\MoveDamageClass")
     */
    protected $moveDamageClass;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $battleOnly = false;

    /**
     * @return MoveDamageClass
     */
    public function getMoveDamageClass(): MoveDamageClass
    {
        return $this->moveDamageClass;
    }

    /**
     * @param MoveDamageClass $moveDamageClass
     *
     * @return self
     */
    public function setMoveDamageClass(MoveDamageClass $moveDamageClass): self
    {
        $this->moveDamageClass = $moveDamageClass;

        return $this;
    }

    /**
     * @return bool
     */
    public function isBattleOnly(): bool
    {
        return $this->battleOnly;
    }

    /**
     * @param bool $battleOnly
     *
     * @return self
     */
    public function setBattleOnly(bool $battleOnly): self
    {
        $this->battleOnly = $battleOnly;

        return $this;
    }

}
