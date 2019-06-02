<?php


namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

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
     * @ORM\Column(type="string")
     *
     * @Gedmo\Slug(fields={"name"}, unique=false)
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
