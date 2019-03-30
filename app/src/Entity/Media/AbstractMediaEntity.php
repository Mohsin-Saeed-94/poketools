<?php
/**
 * @file AbstractMediaEntity.php
 */

namespace App\Entity\Media;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class AbstractMediaEntity
 */
abstract class AbstractMediaEntity
{
    /**
     * @var string
     *
     * @ORM\Column(type="string")
     * @ORM\Id()
     */
    protected $url;

    /**
     * AbstractMediaEntity constructor.
     *
     * @param string|null $url
     */
    public function __construct(?string $url = null)
    {
        if ($url !== null) {
            $this->url = $url;
        }
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getUrl() ?? '';
    }

    /**
     * @return string
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * @param string $url
     *
     * @return self
     */
    public function setUrl(string $url)
    {
        $this->url = $url;

        return $this;
    }
}
