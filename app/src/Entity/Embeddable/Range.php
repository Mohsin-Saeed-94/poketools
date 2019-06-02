<?php


namespace App\Entity\Embeddable;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * A range of values
 *
 * @ORM\Embeddable()
 */
class Range
{

    /**
     * Minimum value
     *
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    protected $min;

    /**
     * Maximum value
     *
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    protected $max;

    /**
     * @param string $range
     *
     * @return self
     */
    public static function fromString(string $range): self
    {
        if (strpos($range, '-') === false) {
            return (new self())->setMin($range)->setMax($range);
        } else {
            $parts = explode('-', $range);

            return (new self())->setMin($parts[0])->setMax($parts[1]);
        }
    }

    /**
     * Compare two range objects for equality.
     *
     * @param Range $a
     * @param Range $b
     *
     * @return bool
     */
    public static function equals(Range $a, Range $b): bool
    {
        return $a->getMin() === $b->getMin()
            && $a->getMax() === $b->getMax();
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        if ($this->getMin() === $this->getMax()) {
            return (string)$this->getMin();
        } else {
            return sprintf('%d-%d', $this->getMin(), $this->getMax());
        }
    }

    /**
     * @return int
     */
    public function getMin(): int
    {
        return $this->min;
    }

    /**
     * @param int $min
     *
     * @return self
     */
    public function setMin(int $min): self
    {
        $this->min = $min;

        return $this;
    }

    /**
     * @return int
     */
    public function getMax(): int
    {
        return $this->max;
    }

    /**
     * @param int $max
     *
     * @return self
     */
    public function setMax(int $max): self
    {
        $this->max = $max;

        return $this;
    }
}
