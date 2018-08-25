<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;


/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass="App\Repository\PalParkAreaRepository")
 */
class PalParkArea extends AbstractDexEntity implements EntityHasNameInterface, EntityHasSlugInterface
{

    use EntityHasNameAndSlugTrait;
}
