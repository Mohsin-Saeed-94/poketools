<?php


namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model\Timestampable\Timestampable;
use Ramsey\Uuid\Uuid;

abstract class AbstractDexEntity
{

    use Timestampable;

    /**
     * @ORM\Id()
     * @ORM\Column(type="uuid_binary_ordered_time", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidOrderedTimeGenerator")
     */
    protected $id;

    /**
     * @return Uuid
     */
    public function getId(): Uuid
    {
        return $this->id;
    }
}
