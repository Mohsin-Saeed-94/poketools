<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;


/**
 * An ability a Pokémon can have, such as Static or Pressure.
 *
 * @ORM\Entity(repositoryClass="App\Repository\AbilityInVersionGroupRepository")
 *
 * @method Ability getParent()
 * @method self setParent(Ability $parent)
 *
 * @ApiResource(
 *     normalizationContext={"groups"={"read"}}
 * )
 * @ApiFilter(SearchFilter::class, properties={"versionGroup": "exact"})
 */
class AbilityInVersionGroup extends AbstractDexEntity implements EntityHasNameInterface, EntityHasSlugInterface, EntityGroupedByVersionGroupInterface, EntityHasFlavorTextInterface, EntityHasDescriptionInterface, EntityHasParentInterface
{

    use EntityHasNameAndSlugTrait;
    use EntityGroupedByVersionGroupTrait;
    use EntityHasFlavorTextTrait;
    use EntityHasDescriptionTrait;
    use EntityHasParentTrait;

    /**
     * @var Ability
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Ability", inversedBy="children")
     */
    protected $parent;
}
