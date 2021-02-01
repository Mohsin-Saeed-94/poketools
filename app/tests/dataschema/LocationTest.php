<?php

namespace App\Tests\dataschema;

use App\Tests\dataschema\Filter\EntityHasVersionGroup;
use App\Tests\dataschema\Filter\RegionHasMap;
use App\Tests\dataschema\Filter\SingleDefault;
use App\Tests\dataschema\Filter\YamlIdentifierExists;
use App\Tests\Traits\YamlParserTrait;

/**
 * Test Location data
 *
 * @group data
 * @group location
 * @coversNothing
 */
class LocationTest extends DataSchemaTestCase
{

    use YamlParserTrait;

    /**
     * Test data matches schema
     *
     * @dataProvider dataProvider
     */
    public function testData(array $data): void
    {
        $this->assertDataSchema('location', $data);
    }

    public function dataProvider()
    {
        return $this->buildYamlDataProvider('location');
    }

    /**
     * @inheritDoc
     */
    protected function getFilters(): array
    {
        return [
            'object' => [
                'singleDefault' => new SingleDefault(),
            ],
            'string' => [
                'versionGroupIdentifier' => new YamlIdentifierExists('version_group'),
                'regionIdentifier' => new YamlIdentifierExists('region'),
                'regionInVersionGroup' => new EntityHasVersionGroup('region'),
                'regionHasMap' => new RegionHasMap(),
                'locationIdentifier' => new YamlIdentifierExists('location'),
                'locationInVersionGroup' => new EntityHasVersionGroup('location'),
            ],
        ];
    }

}
