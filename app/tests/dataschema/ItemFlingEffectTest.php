<?php

namespace App\Tests\dataschema;


use App\Tests\Traits\CsvParserTrait;

/**
 * Test Item Fling Effect
 *
 * @group data
 * @group item_fling_effect
 * @coversNothing
 */
class ItemFlingEffectTest extends DataSchemaTestCase
{

    use CsvParserTrait;

    /**
     * Test data matches schema
     *
     * @dataProvider dataProvider
     */
    public function testData(array $row): void
    {
        $this->assertDataSchema('item_fling_effect', $row);
    }

    public function dataProvider()
    {
        return $this->buildCsvDataProvider('item_fling_effect', 'identifier');
    }

}
