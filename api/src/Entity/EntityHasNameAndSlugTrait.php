<?php


namespace App\Entity;

use Knp\DoctrineBehaviors\Model\Sluggable\Sluggable;
use Doctrine\ORM\Mapping as ORM;

/**
 * Default implementation of App\Entity\EntityHasSlugInterface
 *
 * This will also implement App\Entity\EntityHasNameInterface as a consequence
 * of needing a field to generate the slug from.
 */
trait EntityHasNameAndSlugTrait
{
    use EntityHasNameTrait;
    use Sluggable {
        generateSlug as baseGenerateSlug;
        setSlug as baseSetSlug;
    }

    /**
     * URL slug (not necessarily unique)
     *
     * @var string
     */
    protected $slug;

    /**
     * Store if the user has set a custom slug
     *
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $customSlug = false;

    /**
     * {@inheritdoc}
     */
    public function getSluggableFields()
    {
        return ['name'];
    }

    /**
     * Only generate slugs if one has not been custom set
     */
    public function generateSlug()
    {
        if (!$this->customSlug || empty($this->slug)) {
            $this->baseGenerateSlug();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setSlug($slug)
    {
        $this->baseSetSlug($slug);

        if (empty($slug)) {
            $this->customSlug = false;
        } else {
            $this->customSlug = true;
        }
    }

}
