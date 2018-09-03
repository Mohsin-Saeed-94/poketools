<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * An evolution type, such as “level” or “trade”.
 *
 * @ORM\Entity(repositoryClass="App\Repository\EvolutionTriggerRepository")
 */
class EvolutionTrigger extends AbstractDexEntity implements EntityHasNameInterface, EntityHasSlugInterface, EntityIsSortableInterface
{
    use EntityHasNameAndSlugTrait;
    use EntityIsSortableTrait;
}
