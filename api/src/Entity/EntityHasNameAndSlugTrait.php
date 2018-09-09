<?php


namespace App\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;

/**
 * Default implementation of App\Entity\EntityHasSlugInterface
 *
 * This will also implement App\Entity\EntityHasNameInterface as a consequence
 * of needing a field to generate the slug from.
 */
trait EntityHasNameAndSlugTrait
{
    use EntityHasNameTrait;

    /**
     * URL slug
     *
     * @var string|null
     *
     * @ORM\Column(type="string", unique=true)
     *
     * @Gedmo\Slug(fields={"name"}, handlers={@Gedmo\SlugHandler(class="App\Handler\SluggableGroupedHandler")})
     */
    protected $slug;

    /**
     * @return null|string
     */
    public function getSlug(): ?string
    {
        return $this->slug;
    }

    /**
     * @param null|string $slug
     *
     * @return self
     */
    public function setSlug(?string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }
}
