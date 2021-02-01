<?php


namespace App\Tests\dataschema\Filter;


use App\Tests\Traits\CsvParserTrait;
use Ds\Set;
use Opis\JsonSchema\IFilter;

/**
 * Checks that the given identifier exists in a CSV file
 */
class CsvIdentifierExists implements IFilter
{

    use CsvParserTrait;

    private Set $identifiers;

    /**
     * CsvIdentifierExists constructor.
     *
     * @param string $entityType
     * @param string $column
     *   The column in the CSV file to use as the identifier
     */
    public function __construct(string $entityType, string $column = 'identifier')
    {
        $this->identifiers = new Set();
        $reader = $this->getCsvReader($entityType);
        foreach ($reader as $row) {
            $this->identifiers->add($row[$column]);
        }
    }

    /**
     * @param $data
     * @param array $args
     *
     * @return bool
     */
    public function validate($data, array $args): bool
    {
        return $this->identifiers->contains((string)$data);
    }

}
