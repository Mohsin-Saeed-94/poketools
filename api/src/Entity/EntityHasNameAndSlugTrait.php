<?php


namespace App\Entity;

use Knp\DoctrineBehaviors\Model\Sluggable\Sluggable;

/**
 * Default implementation of App\Entity\EntityHasSlugInterface
 *
 * This will also implement App\Entity\EntityHasNameInterface as a consequence
 * of needing a field to generate the slug from.
 */
trait EntityHasNameAndSlugTrait
{
    use EntityHasNameTrait;
    use Sluggable;

    /**
     * URL slug (not necessarily unique)
     *
     * @var string
     */
    protected $slug;

    /**
     * {@inheritdoc}
     */
    public function getSluggableFields()
    {
        return ['name'];
    }
}
