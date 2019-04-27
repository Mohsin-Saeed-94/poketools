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
    use MediaEntityTrait;

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

}
