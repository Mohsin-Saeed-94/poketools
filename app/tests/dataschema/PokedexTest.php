<?php

namespace App\Tests\dataschema;


use App\Tests\dataschema\Filter\YamlIdentifierExists;
use App\Tests\Traits\YamlParserTrait;

/**
 * Test Pokedex
 *
 * @group data
 * @group pokedex
 * @coversNothing
 */
class PokedexTest extends DataSchemaTestCase
{

    use YamlParserTrait;

    /**
     * Test data matches schema
     *
     * @dataProvider dataProvider
     */
    public function testData(array $data): void
    {
        $this->assertDataSchema('pokedex', $data);
    }

    public function dataProvider()
    {
        return $this->buildYamlDataProvider('pokedex');
    }

    /**
     * @inheritDoc
     */
    protected function getFilters(): array
    {
        return [
            'string' => [
                'versionGroupIdentifier' => new YamlIdentifierExists('version_group'),
            ],
        ];
    }
}
