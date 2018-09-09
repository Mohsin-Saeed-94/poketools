<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A Pokéblock color associated with a contest type.
 *
 * @ORM\Entity(repositoryClass="App\Repository\PokeblockColorRepository")
 */
class PokeblockColor extends AbstractDexEntity implements EntityHasNameInterface, EntityHasSlugInterface
{

    use EntityHasNameAndSlugTrait;
}
