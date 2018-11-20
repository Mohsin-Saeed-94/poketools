<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;


/**
 * A distinct area of Pal Park in which Pokémon appear.
 *
 * @ORM\Entity(repositoryClass="App\Repository\PalParkAreaRepository")
 */
class PalParkArea extends AbstractDexEntity implements EntityHasNameInterface, EntityHasSlugInterface
{

    use EntityHasNameAndSlugTrait;
}
