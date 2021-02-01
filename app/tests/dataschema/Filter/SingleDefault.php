<?php


namespace App\Tests\dataschema\Filter;


use Opis\JsonSchema\IFilter;

/**
 * Make sure only one element is labeled default
 */
class SingleDefault implements IFilter
{

    /**
     * @var bool
     */
    private $allowNoDefaults;

    /**
     * SingleDefault constructor.
     *
     * @param bool $allowNoDefaults
     */
    public function __construct(bool $allowNoDefaults = false)
    {
        $this->allowNoDefaults = $allowNoDefaults;
    }

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
            if ($defaults > 1) {
                break;
            }
        }

        return $this->allowNoDefaults ? $defaults <= 1 : $defaults === 1;
    }

}
