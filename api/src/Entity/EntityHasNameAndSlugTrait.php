<?php


namespace App\Entity;

use Knp\DoctrineBehaviors\Model\Sluggable\SluggableMethods;
use Knp\DoctrineBehaviors\Model\Sluggable\SluggableProperties;
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
    use SluggableProperties;
    use SluggableMethods;

    /**
     * {@inheritdoc}
     */
    public function getSluggableFields()
    {
        return ['name'];
    }
}
