<?php


namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Default implementation of App\Entity\EntityHasNameInterface
 */
trait EntityHasNameTrait
{

    /**
     * Entity name
     *
     * @var string
     *
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     *
     * @ApiProperty(iri="http://schema.org/name")
     */
    protected $name;

    /**
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return self
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }
}
