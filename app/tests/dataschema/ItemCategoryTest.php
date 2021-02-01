<?php

namespace App\Tests\dataschema;


use App\Tests\dataschema\Filter\CsvIdentifierExists;
use App\Tests\Traits\CsvParserTrait;

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
     *
     * @dataProvider dataProvider
     */
    public function testData(array $row): void
    {
        $this->assertDataSchema('item_category', $row);
    }

    public function dataProvider()
    {
        return $this->buildCsvDataProvider('item_category', 'identifier');
    }

    /**
     * @inheritDoc
     */
    protected function getFilters(): array
    {
        return [
            'string' => [
                'categoryIdentifier' => new CsvIdentifierExists('item_category'),
            ],
        ];
    }

}
