<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * The habitat of a Pokémon, as given in the FireRed/LeafGreen version Pokédex.
 *
 * Not valid for Pokémon that do not appear in FireRed/LeafGreen.
 *
 * @ORM\Entity(repositoryClass="App\Repository\PokemonHabitatRepository")
 */
class PokemonHabitat extends AbstractDexEntity implements EntityHasNameInterface, EntityHasSlugInterface
{

    use EntityHasNameAndSlugTrait;
}
