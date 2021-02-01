<?php

namespace App\Tests\dataschema;


use App\Tests\Traits\CsvParserTrait;

/**
 * Test Item Flag
 *
 * @group data
 * @group item_flag
 * @coversNothing
 */
class ItemFlagTest extends DataSchemaTestCase
{

    use CsvParserTrait;

    /**
     * Test data matches schema
     *
     * @dataProvider dataProvider
     */
    public function testData(array $row): void
    {
        $this->assertDataSchema('item_flag', $row);
    }

    public function dataProvider()
    {
        return $this->buildCsvDataProvider('item_flag', 'identifier');
    }

}
