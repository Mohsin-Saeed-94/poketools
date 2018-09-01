<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


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
    protected $damageClass;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $battleOnly = false;

    /**
     * @return MoveDamageClass
     */
    public function getDamageClass(): ?MoveDamageClass
    {
        return $this->damageClass;
    }

    /**
     * @param MoveDamageClass $damageClass
     *
     * @return self
     */
    public function setDamageClass(?MoveDamageClass $damageClass): self
    {
        $this->damageClass = $damageClass;

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
