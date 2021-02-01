<?php

namespace App\Tests\dataschema;


use App\Tests\dataschema\Filter\EntityHasVersionGroup;
use App\Tests\dataschema\Filter\LocationAreaHasShop;
use App\Tests\dataschema\Filter\LocationHasArea;
use App\Tests\dataschema\Filter\YamlIdentifierExists;
use App\Tests\Traits\CsvParserTrait;

/**
 * Test Shop Item
 *
 * @group data
 * @group shop_item
 * @coversNothing
 */
class ShopItemTest extends DataSchemaTestCase
{

    use CsvParserTrait;

    /**
     * Test data matches schema
     *
     * @dataProvider dataProvider
     */
    public function testData(array $row): void
    {
        $this->assertDataSchema('shop_item', $row);
    }

    public function dataProvider()
    {
        return $this->buildCsvDataProvider('shop_item');
    }

    /**
     * @inheritDoc
     */
    protected function getFilters(): array
    {
        return [
            'string' => [
                'versionGroupIdentifier' => new YamlIdentifierExists('version_group'),
                'locationIdentifier' => new YamlIdentifierExists('location'),
                'locationInVersionGroup' => new EntityHasVersionGroup('location'),
                'itemIdentifier' => new YamlIdentifierExists('item'),
                'itemInVersionGroup' => new EntityHasVersionGroup('item'),
                'locationHasArea' => new LocationHasArea(),
                'locationAreaHasShop' => new LocationAreaHasShop(),
            ],
        ];
    }

}
