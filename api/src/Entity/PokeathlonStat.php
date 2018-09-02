<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * A Pokéathlon stat, such as “Stamina” or “Jump”.
 *
 * @ApiResource()
 * @ORM\Entity(repositoryClass="App\Repository\PokeathlonStatRepository")
 */
class PokeathlonStat extends AbstractDexEntity implements EntityHasNameInterface, EntityHasSlugInterface, EntityIsSortableInterface
{

    use EntityHasNameAndSlugTrait;
    use EntityIsSortableTrait;
}
