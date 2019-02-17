<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * A Pokéathlon stat, such as “Stamina” or “Jump”.
 *
 * @ORM\Entity(repositoryClass="App\Repository\PokeathlonStatRepository")
 */
class PokeathlonStat extends AbstractDexEntity implements EntityHasNameInterface, EntityHasSlugInterface, EntityIsSortableInterface
{

    use EntityHasNameAndSlugTrait;
    use EntityIsSortableTrait;
}
