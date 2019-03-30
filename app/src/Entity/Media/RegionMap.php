<?php

namespace App\Entity\Media;

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
 * @ORM\Entity()
 */
class RegionMap extends AbstractMediaEntity implements EntityHasNameInterface, EntityHasSlugInterface, EntityIsSortableInterface
{
    use EntityHasNameAndSlugTrait;
    use EntityIsSortableTrait;

    /**
     * @var RegionInVersionGroup
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\RegionInVersionGroup", inversedBy="maps")
     * @ORM\Id()
     * @Assert\NotBlank()
     */
    protected $region;

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
