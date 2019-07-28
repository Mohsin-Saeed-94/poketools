<?php


namespace App\Tests\dataschema\Filter;


use App\Tests\data\CsvParserTrait;
use App\Tests\data\YamlParserTrait;
use Opis\JsonSchema\IFilter;

/**
 * Ensures that a given entity lists the data in a given property
 *
 * It is assumed that the given entity exists
 */
class InEntityList implements IFilter
{
    use CsvParserTrait;
    use YamlParserTrait;

    /**
     * @var string
     */
    private $entityType;

    /**
     * @var string
     */
    private $property;

    /**
     * Cache lookup results
     *
     * @var array
     */
    private $lists = [];

    /**
     * EntityHasVersionGroup constructor.
     *
     * @param string $entityType
     * @param string $property
     */
    public function __construct(string $entityType, string $property = 'version_groups')
    {
        $this->entityType = $entityType;
        $this->property = $property;
    }

    /**
     * @param $data
     * @param array $args
     *
     * @return bool
     */
    public function validate($data, array $args): bool
    {
        $identifier = $this->entityType.'/'.$data;
        if (!isset($this->lists[$identifier])) {
            $entity = $this->loadEntityYaml($identifier);
            $this->lists[$identifier] = array_fill_keys($entity[$this->property], 0);
        }

        return isset($this->lists[$identifier][$data]);
    }
}
