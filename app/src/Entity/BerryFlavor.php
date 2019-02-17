<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A berry flavor associated with a contest type.
 *
 * @ORM\Entity(repositoryClass="App\Repository\BerryFlavorRepository")
 */
class BerryFlavor extends AbstractDexEntity implements EntityHasNameInterface, EntityHasSlugInterface
{

    use EntityHasNameAndSlugTrait;
}
