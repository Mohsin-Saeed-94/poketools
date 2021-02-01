<?php


namespace App\Tests\dataschema\Filter;


use App\Tests\Traits\CsvParserTrait;
use App\Tests\Traits\VersionVersionGroupTrait;
use Opis\JsonSchema\IFilter;

/**
 * Ensures that a version is in the version group
 *
 * It is assumed that the given entity exists
 */
class VersionInVersionGroup implements IFilter
{

    use CsvParserTrait;
    use VersionVersionGroupTrait;

    /**
     * @param $data
     * @param array $args
     *
     * @return bool
     */
    public function validate($data, array $args): bool
    {
        $versionGroup = $args['versionGroup'];

        return $this->getVersionVersionGroup($data) === $versionGroup;
    }

}
