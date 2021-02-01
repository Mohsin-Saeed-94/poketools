<?php

namespace App\Tests\Traits;


use Generator;
use League\Csv\Exception;
use League\Csv\Reader;

trait CsvParserTrait
{

    /**
     * Build a data provider for a CSV file.
     *
     * @param string $path
     *  Path relative to data directory, without trailing extension
     * @param array|string|null $keys
     *  The keys to use as the dataset label.  Pass null to not present keys.
     *
     * @return Generator
     * @throws Exception
     */
    protected function buildCsvDataProvider(string $path, array|string|null $keys = null): Generator
    {
        foreach ($this->getCsvReader($path) as $row) {
            if ($keys === null) {
                yield [$row];
            } elseif (is_string($keys)) {
                // PHPUnit will discard numeric keys
                $key = is_numeric($row[$keys]) ? '#'.$row[$keys] : $row[$keys];
                yield $key => [$row];
            } elseif (is_array($keys)) {
                $keyValues = [];
                foreach ($keys as $key) {
                    $keyValues[] = sprintf('[%s => %s]', $key, $row[$key]);
                }
                $dataSetLabel = implode(', ', $keyValues);
                yield $dataSetLabel => [$row];
            }
        }
    }

    /**
     * @param string $path
     *
     * @return Reader
     * @throws Exception
     */
    protected function getCsvReader(string $path): Reader
    {
        $path = implode('/', [dirname(__FILE__, 3), 'resources/data', $path]).'.csv';
        $reader = Reader::createFromPath($path);
        $reader->setHeaderOffset(0);

        return $reader;
    }

}
