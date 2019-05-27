<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * A feature in a version group
 *
 * The presence (or lack thereof) of features in a version group will enable
 * or disable certain site functionality on that version group's pages.
 *
 * @ORM\Entity(repositoryClass="App\Repository\FeatureRepository")
 */
class Feature extends AbstractDexEntity implements EntityHasSlugInterface, EntityHasDescriptionInterface
{
    use EntityHasDescriptionTrait;

    /**
     * @var string|null
     * @ORM\Column(type="string")
     */
    protected $slug;

    /**
     * @return string|null
     */
    public function getSlug(): ?string
    {
        return $this->slug;
    }

    /**
     * @param string|null $slug
     *
     * @return Feature
     */
    public function setSlug(?string $slug): Feature
    {
        $this->slug = $slug;

        return $this;
    }
}
