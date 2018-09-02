<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * The shape of a Pokémon’s body. Appears in the Pokédex starting with
 * Generation IV.
 *
 * @ApiResource()
 * @ORM\Entity(repositoryClass="App\Repository\PokemonShapeInVersionGroupRepository")
 *
 * @method PokemonShape getParent()
 * @method self setParent(PokemonShape $parent)
 */
class PokemonShapeInVersionGroup extends AbstractDexEntity implements EntityHasNameInterface, EntityHasSlugInterface, EntityHasDescriptionInterface, EntityHasIconInterface, EntityHasParentInterface, EntityGroupedByVersionGroupInterface
{

    use EntityHasNameAndSlugTrait;
    use EntityHasDescriptionTrait;
    use EntityHasIconTrait;
    use EntityHasParentTrait;
    use EntityGroupedByVersionGroupTrait;

    /**
     * @var PokemonShape
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\PokemonShape", inversedBy="children")
     */
    protected $parent;

    /**
     * A taxonomy name for this shape, roughly corresponding to a family name
     * in zoological taxonomy.
     *
     * @var string
     *
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     */
    private $taxonomyName;

    /**
     * @return string
     */
    public function getTaxonomyName(): ?string
    {
        return $this->taxonomyName;
    }

    /**
     * @param string $taxonomyName
     *
     * @return self
     */
    public function setTaxonomyName(string $taxonomyName): self
    {
        $this->taxonomyName = $taxonomyName;

        return $this;
    }
}
