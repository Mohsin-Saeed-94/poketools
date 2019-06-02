<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A Berry firmness, such as “hard” or “very soft”.
 *
 * @ORM\Entity(repositoryClass="App\Repository\BerryFirmnessRepository")
 */
class BerryFirmness extends AbstractDexEntity implements EntityHasNameInterface, EntityHasSlugInterface
{

    use EntityHasNameAndSlugTrait;
}
