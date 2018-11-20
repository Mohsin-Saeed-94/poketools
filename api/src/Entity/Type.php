<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TypeRepository")
 */
class Type extends AbstractDexEntity implements EntityHasNameInterface, EntityHasSlugInterface
{

    use EntityHasNameAndSlugTrait;

    /**
     * The damage class this type’s moves had before Generation 4, if applicable
     *
     * @var MoveDamageClass|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\MoveDamageClass", fetch="EAGER")
     */
    protected $damageClass;

    /**
     * Is this type normally shown to the player or used to implement a game
     * mechanic?
     *
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $hidden = false;

    /**
     * @return MoveDamageClass|null
     */
    public function getDamageClass(): ?MoveDamageClass
    {
        return $this->damageClass;
    }

    /**
     * @param MoveDamageClass|null $damageClass
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
    public function isHidden(): bool
    {
        return $this->hidden;
    }

    /**
     * @param bool $hidden
     *
     * @return self
     */
    public function setHidden(bool $hidden): self
    {
        $this->hidden = $hidden;

        return $this;
    }
}
