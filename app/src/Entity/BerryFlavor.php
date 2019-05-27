<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * A berry flavor associated with a contest type.
 *
 * @ORM\Entity(repositoryClass="App\Repository\BerryFlavorRepository")
 */
class BerryFlavor extends AbstractDexEntity implements EntityHasNameInterface, EntityHasSlugInterface
{

    use EntityHasNameAndSlugTrait;

    /**
     * The corresponding Contest type
     *
     * @var ContestType
     *
     * @ORM\OneToOne(targetEntity="App\Entity\ContestType", mappedBy="berryFlavor")
     */
    protected $contestType;

    /**
     * @return ContestType
     */
    public function getContestType(): ContestType
    {
        return $this->contestType;
    }

    /**
     * @param ContestType $contestType
     *
     * @return BerryFlavor
     */
    public function setContestType(ContestType $contestType): BerryFlavor
    {
        $this->contestType = $contestType;
        $contestType->setBerryFlavor($this);

        return $this;
    }
}
