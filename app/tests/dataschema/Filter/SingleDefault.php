<?php


namespace App\Tests\dataschema\Filter;


use Opis\JsonSchema\IFilter;

/**
 * Make sure only one element is labeled default
 */
class SingleDefault implements IFilter
{

    /**
     * @param $data
     * @param array $args
     *
     * @return bool
     */
    public function validate($data, array $args): bool
    {
        $defaults = 0;
        foreach ($data as $datum) {
            if (isset($datum->default) && $datum->default === true) {
                $defaults++;
            }
        }

        return $defaults === 1;
    }
}
