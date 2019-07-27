<?php

namespace App\Tests\dataschema;


use App\Tests\data\CsvParserTrait;
use App\Tests\dataschema\Filter\CsvIdentifierExists;

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
        $this->assertDataSchema('item_category', $allData);
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
