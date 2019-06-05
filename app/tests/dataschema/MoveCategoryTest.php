<?php

namespace App\Tests\dataschema;


use App\Tests\data\CsvParserTrait;

/**
 * Test Move Category
 *
 * @group data
 * @group move_category
 * @coversNothing
 */
class MoveCategoryTest extends DataSchemaTestCase
{
    use CsvParserTrait;

    /**
     * Test data matches schema
     */
    public function testData(): void
    {
        $allData = $this->getIteratorForCsv('move_category');
        self::assertDataSchema('move_category', $allData);
    }
}
