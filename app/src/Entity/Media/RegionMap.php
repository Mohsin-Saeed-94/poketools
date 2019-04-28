<?php

namespace App\Entity\Media;

use App\Entity\AbstractDexEntity;
use App\Entity\EntityHasNameAndSlugTrait;
use App\Entity\EntityHasNameInterface;
use App\Entity\EntityHasSlugInterface;
use App\Entity\EntityIsSortableInterface;
use App\Entity\EntityIsSortableTrait;
use App\Entity\RegionInVersionGroup;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Region Map
 *
 * @ORM\Entity(repositoryClass="App\Repository\Media\RegionMapRepository")
 */
class RegionMap extends AbstractDexEntity implements EntityHasNameInterface, EntityHasSlugInterface, EntityIsSortableInterface
{
    use MediaEntityTrait, EntityHasNameAndSlugTrait {
        MediaEntityTrait::__toString insteadof EntityHasNameAndSlugTrait;
    }
    use EntityIsSortableTrait;

    /**
     * @var RegionInVersionGroup
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\RegionInVersionGroup", inversedBy="maps")
     * @Assert\NotBlank()
     */
    protected $region;

    /**
     * RegionMap constructor.
     *
     * @param string|null $url
     */
    public function __construct(?string $url = null)
    {
        if ($url !== null) {
            $this->url = $url;
        }
    }

    /**
     * @return RegionInVersionGroup
     */
    public function getRegion(): ?RegionInVersionGroup
    {
        return $this->region;
    }

    /**
     * @param RegionInVersionGroup $region
     *
     * @return self
     */
    public function setRegion(RegionInVersionGroup $region): self
    {
        $this->region = $region;

        return $this;
    }
}
