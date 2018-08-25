<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;


/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass="App\Repository\PokemonHabitatRepository")
 */
class PokemonHabitat extends AbstractDexEntity implements EntityHasNameInterface, EntityHasSlugInterface
{

    use EntityHasNameAndSlugTrait;
}
