<?php

namespace App\Tests\dataschema;

use App\Tests\data\CsvParserTrait;
use App\Tests\dataschema\Filter\CsvIdentifierExists;


/**
 * Test Characteristic
 *
 * @group data
 * @group characteristic
 * @coversNothing
 */
class CharacteristicTest extends DataSchemaTestCase
{
    use CsvParserTrait;

    /**
     * Test data matches schema
     */
    public function testData(): void
    {
        $allData = $this->getIteratorForCsv('characteristic');
        $this->assertDataSchema('characteristic', $allData);
    }

    /**
     * @inheritDoc
     */
    protected function getFilters(): array
    {
        return [
            'string' => [
                'statIdentifier' => new CsvIdentifierExists('stat'),
            ],
        ];
    }
}
