<?php


namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Default implementation of App\Entity\EntityGroupedByVersionInterface
 */
trait EntityGroupedByVersionTrait
{

    /**
     * @var Version
     *
     * @ORM\ManyToOne(targetEntity="EntityGroupedByVersion")
     * @Assert\NotNull()
     */
    protected $version;

    /**
     * @return Version
     */
    public function getVersion(): Version
    {
        return $this->version;
    }

    /**
     * @param Version $version
     *
     * @return self
     */
    public function setVersion(Version $version): self
    {
        $this->version = $version;

        return $this;
    }

    public function getGroup(): GroupableInterface
    {
        return $this->getVersion();
    }

    public static function getGroupField(): string
    {
        return 'version';
    }
}
