<?php

namespace App\Tests\dataschema;

use App\Tests\dataschema\Filter\CsvIdentifierExists;
use App\Tests\Traits\CsvParserTrait;


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
     *
     * @dataProvider dataProvider
     */
    public function testData(array $row): void
    {
        $this->assertDataSchema('characteristic', $row);
    }

    public function dataProvider()
    {
        return $this->buildCsvDataProvider('characteristic', ['stat', 'iv_determinator']);
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
