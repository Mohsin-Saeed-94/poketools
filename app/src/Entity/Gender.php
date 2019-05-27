<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A Pokémon gender
 *
 * @ORM\Entity(repositoryClass="App\Repository\GenderRepository")
 */
class Gender extends AbstractDexEntity implements EntityHasNameInterface, EntityHasSlugInterface, EntityIsSortableInterface
{

    use EntityHasNameAndSlugTrait;
    use EntityIsSortableTrait;
}
