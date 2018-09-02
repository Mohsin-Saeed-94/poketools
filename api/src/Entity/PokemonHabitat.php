<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * The habitat of a Pokémon, as given in the FireRed/LeafGreen version Pokédex.
 *
 * Not valid for Pokémon that do not appear in FireRed/LeafGreen.
 *
 * @ApiResource()
 * @ORM\Entity(repositoryClass="App\Repository\PokemonHabitatRepository")
 */
class PokemonHabitat extends AbstractDexEntity implements EntityHasNameInterface, EntityHasSlugInterface
{

    use EntityHasNameAndSlugTrait;
}
