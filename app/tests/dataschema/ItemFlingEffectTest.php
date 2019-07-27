<?php

namespace App\Tests\dataschema;


use App\Tests\data\CsvParserTrait;

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
     */
    public function testData(): void
    {
        $allData = $this->getIteratorForCsv('item_fling_effect');
        $this->assertDataSchema('item_fling_effect', $allData);
    }
}
