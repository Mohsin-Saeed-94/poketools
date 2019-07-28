<?php


namespace App\Tests\dataschema\Filter;


use App\Tests\data\CsvParserTrait;
use Opis\JsonSchema\IFilter;

/**
 * Checks that the given identifier exists in a CSV file
 */
class CsvIdentifierExists implements IFilter
{
    use CsvParserTrait;

    /**
     * @var string
     */
    private $entityType;

    /**
     * A list of identifiers in the CSV file
     *
     * @var array
     */
    private $identifiers = [];

    /**
     * CsvIdentifierExists constructor.
     *
     * @param string $entityType
     * @param string $column
     *   The column in the CSV file to use as the identifier
     */
    public function __construct(string $entityType, string $column = 'identifier')
    {
        $this->entityType = $entityType;
        $this->init($column);
    }

    /**
     * Extract the identifiers from the CSV
     *
     * @param string $column
     */
    private function init(string $column): void
    {
        $it = $this->getIteratorForCsv($this->entityType);
        $this->identifiers = array_column($it, $column);
    }

    /**
     * @param $data
     * @param array $args
     *
     * @return bool
     */
    public function validate($data, array $args): bool
    {
        return in_array((string)$data, $this->identifiers, true);
    }
}
