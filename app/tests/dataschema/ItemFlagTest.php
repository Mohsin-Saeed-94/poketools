<?php

namespace App\Tests\dataschema;


use App\Tests\data\CsvParserTrait;

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
     */
    public function testData(): void
    {
        $allData = $this->getIteratorForCsv('item_flag');
        $this->assertDataSchema('item_flag', $allData);
    }
}
