<?php
/**
 * @file PokemonArt.php
 */

namespace App\Entity\Media;

use App\Entity\PokemonForm;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Pokemon concept art
 *
 * @ORM\Entity()
 */
class PokemonArt extends AbstractMediaEntity
{
    /**
     * @var PokemonForm
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\PokemonForm", inversedBy="art")
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
