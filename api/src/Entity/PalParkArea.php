<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * A distinct area of Pal Park in which Pokémon appear.
 *
 * @ApiResource()
 * @ORM\Entity(repositoryClass="App\Repository\PalParkAreaRepository")
 */
class PalParkArea extends AbstractDexEntity implements EntityHasNameInterface, EntityHasSlugInterface
{

    use EntityHasNameAndSlugTrait;
}
