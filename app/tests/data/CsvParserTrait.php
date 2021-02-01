<?php
/**
 * @file CsvParserTrait.php
 */

namespace App\Tests\data;


use Generator;
use Symfony\Component\Serializer\Encoder\CsvEncoder;

trait CsvParserTrait
{
    /**
     * Build a data provider for the given data array
     *
     * @param array $data
     * @param array $keys
     *   The keys in $array to use as the data set label.
     *
     * @return Generator
     */
    protected function buildArrayDataProvider(array $data, array $keys): Generator
    {
        foreach ($data as $datum) {
            $keyValues = [];
            foreach ($keys as $key) {
                $keyValues[] = sprintf('["%s" => "%s"]', $key, $datum[$key]);
            }
            $dataSetLabel = implode(', ', $keyValues);
            yield $dataSetLabel => [$datum];
        }
    }

    /**
     * @param string $path
     *
     * @return array
     */
    protected function getIteratorForCsv(string $path): array
    {
        $path = realpath(__DIR__.'/../../resources/data').'/'.ltrim($path, '/').'.csv';
        $encoder = new CsvEncoder();
        $data = $encoder->decode(file_get_contents($path), 'csv');

        return $data;
    }
}
