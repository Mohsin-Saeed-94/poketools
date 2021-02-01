<?php

namespace App\Tests\dataschema;


use App\Tests\dataschema\Filter\CsvIdentifierExists;
use App\Tests\dataschema\Filter\EntityHasVersionGroup;
use App\Tests\dataschema\Filter\RangeFilter;
use App\Tests\dataschema\Filter\TypeInVersionGroup;
use App\Tests\dataschema\Filter\YamlIdentifierExists;
use App\Tests\Traits\YamlParserTrait;

/**
 * Test Item
 *
 * @group data
 * @group item
 * @coversNothing
 */
class ItemTest extends DataSchemaTestCase
{

    use YamlParserTrait;

    /**
     * Test data matches schema
     *
     * @dataProvider dataProvider
     */
    public function testData(array $data): void
    {
        $this->assertDataSchema('item', $data);
    }

    public function dataProvider()
    {
        return $this->buildYamlDataProvider('item');
    }

    /**
     * @inheritDoc
     */
    protected function getFilters(): array
    {
        return [
            'string' => [
                'versionGroupIdentifier' => new YamlIdentifierExists('version_group'),
                'categoryIdentifier' => new CsvIdentifierExists('item_category'),
                'pocketIdentifier' => new YamlIdentifierExists('item_pocket'),
                'pocketInVersionGroup' => new EntityHasVersionGroup('item_pocket'),
                'flagIdentifier' => new CsvIdentifierExists('item_flag'),
                'flingEffectIdentifier' => new CsvIdentifierExists('item_fling_effect'),
                'moveIdentifier' => new YamlIdentifierExists('move'),
                'moveInVersionGroup' => new EntityHasVersionGroup('move'),
                'berryFirmnessIdentifier' => new CsvIdentifierExists('berry_firmness'),
                'typeIdentifier' => new CsvIdentifierExists('type'),
                'typeInVersionGroup' => new TypeInVersionGroup(),
                'berryFlavorIdentifier' => new CsvIdentifierExists('berry_flavor'),
                'range' => new RangeFilter(),
            ],
        ];
    }

}
