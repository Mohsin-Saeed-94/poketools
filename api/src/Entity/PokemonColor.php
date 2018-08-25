<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Validator\CssColor as AssertCssColor;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass="App\Repository\PokemonColorRepository")
 */
class PokemonColor extends AbstractDexEntity implements EntityHasNameInterface, EntityHasSlugInterface, EntityIsSortableInterface
{

    use EntityHasNameAndSlugTrait;
    use EntityIsSortableTrait;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     * @AssertCssColor()
     */
    protected $cssColor;

    /**
     * @return string
     */
    public function getCssColor(): string
    {
        return $this->cssColor;
    }

    /**
     * @param string $cssColor
     *
     * @return self
     */
    public function setCssColor(string $cssColor): self
    {
        $this->cssColor = $cssColor;

        return $this;
    }
}
