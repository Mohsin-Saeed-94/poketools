<?php

namespace App\Tests\dataschema;


use App\Tests\Traits\CsvParserTrait;

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
     *
     * @dataProvider dataProvider
     */
    public function testData(array $row): void
    {
        $this->assertDataSchema('move_category', $row);
    }

    public function dataProvider()
    {
        return $this->buildCsvDataProvider('move_category', 'identifier');
    }

}
