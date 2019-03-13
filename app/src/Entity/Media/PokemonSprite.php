<?php
/**
 * @file PokemonSprite.php
 */

namespace App\Entity\Media;

use App\Entity\PokemonForm;
use Doctrine\ORM\Mapping as ORM;

/**
 * Pokemon Sprite
 *
 * @ORM\Entity()
 */
class PokemonSprite extends AbstractMediaEntity
{
    /**
     * @var PokemonForm
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\PokemonForm", inversedBy="sprites")
     * @ORM\Id()
     */
    protected $pokemonForm;

    /**
     * @return PokemonForm
     */
    public function getPokemonForm(): ?PokemonForm
    {
        return $this->pokemonForm;
    }

    /**
     * @param PokemonForm $pokemonForm
     *
     * @return self
     */
    public function setPokemonForm(PokemonForm $pokemonForm): self
    {
        $this->pokemonForm = $pokemonForm;

        return $this;
    }
}