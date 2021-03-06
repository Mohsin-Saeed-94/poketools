<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * A Stat, such as Attack or Speed.
 *
 * @ORM\Entity(repositoryClass="App\Repository\StatRepository")
 */
class Stat extends AbstractDexEntity implements EntityHasNameInterface, EntityHasSlugInterface, EntityIsSortableInterface
{

    use EntityHasNameAndSlugTrait;
    use EntityIsSortableTrait;

    /**
     * The damage class this stat affects
     *
     * @var MoveDamageClass
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\MoveDamageClass", fetch="EAGER")
     */
    protected $damageClass;

    /**
     * Does this stat only apply in battle?
     *
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
