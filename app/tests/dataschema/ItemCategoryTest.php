<?php

namespace App\Tests\dataschema;


use App\Tests\data\CsvParserTrait;

/**
 * Test Item Category
 *
 * @group data
 * @group item_category
 * @coversNothing
 */
class ItemCategoryTest extends DataSchemaTestCase
{
    use CsvParserTrait;

    /**
     * Test data matches schema
     */
    public function testData(): void
    {
        $allData = $this->getIteratorForCsv('item_category');
        self::assertDataSchema('item_category', $allData);
    }
}
