<?php
/**
 * @file MediaEntityTrait.php
 */

namespace App\Entity\Media;


trait MediaEntityTrait
{
    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    protected $url;

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
