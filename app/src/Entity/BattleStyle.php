<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Battle Palace style.
 *
 * @ORM\Entity(repositoryClass="App\Repository\BattleStyleRepository")
 */
class BattleStyle extends AbstractDexEntity implements EntityHasNameInterface, EntityHasSlugInterface
{

    use EntityHasNameAndSlugTrait;
}
