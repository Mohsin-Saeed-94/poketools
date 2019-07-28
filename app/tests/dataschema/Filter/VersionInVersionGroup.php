<?php


namespace App\Tests\dataschema\Filter;


use App\Tests\data\CsvParserTrait;
use Opis\JsonSchema\IFilter;

/**
 * Ensures that a version is in the version group
 *
 * It is assumed that the given entity exists
 */
class VersionInVersionGroup implements IFilter
{
    use CsvParserTrait;

    /**
     * Cache versions
     *
     * @var array
     */
    private $versions;

    /**
     * EntityHasVersionGroup constructor.
     */
    public function __construct()
    {
        $this->init();
    }

    private function init(): void
    {
        $it = $this->getIteratorForCsv('version');
        $this->versions = array_column($it, 'version_group', 'identifier');
    }

    /**
     * @param $data
     * @param array $args
     *
     * @return bool
     */
    public function validate($data, array $args): bool
    {
        $versionGroup = $args['versionGroup'];

        return $this->versions[$data] === $versionGroup;
    }
}
