<?php


namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

abstract class AbstractDexEntity
{

    /**
     * Unique Id
     *
     * @var int
     *
     * @ORM\Id()
     * @ORM\Column(type="integer", unique=true)
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }
}
