<?php


namespace App\Tests\dataschema\Filter;


use App\Entity\Embeddable\Range;
use Opis\JsonSchema\IFilter;

/**
 * Validate a range
 *
 * Args:
 * - min
 * - max
 */
class RangeFilter implements IFilter
{

    /**
     * @param $data
     * @param array $args
     *
     * @return bool
     */
    public function validate($data, array $args): bool
    {
        $min = $args['min'] ?? PHP_INT_MIN;
        $max = $args['max'] ?? PHP_INT_MAX;

        $range = Range::fromString($data);

        return $range->getMin() >= $min && $range->getMax() <= $max;
    }
}
