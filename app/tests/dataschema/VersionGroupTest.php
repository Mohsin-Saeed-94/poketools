<?php

namespace App\Tests\dataschema;


use App\Tests\dataschema\Filter\CsvIdentifierExists;
use App\Tests\dataschema\Filter\YamlIdentifierExists;
use App\Tests\Traits\YamlParserTrait;

/**
 * Test Version Group
 *
 * @group data
 * @group version_group
 * @coversNothing
 */
class VersionGroupTest extends DataSchemaTestCase
{

    use YamlParserTrait;

    /**
     * Test data matches schema
     *
     * @dataProvider dataProvider
     */
    public function testData(array $data): void
    {
        $this->assertDataSchema('version_group', $data);
    }

    public function dataProvider()
    {
        return $this->buildYamlDataProvider('version_group');
    }

    /**
     * @inheritDoc
     */
    protected function getFilters(): array
    {
        return [
            'integer' => [
                'generationId' => new CsvIdentifierExists('generation', 'id'),
                'typeChartId' => new YamlIdentifierExists('type_chart'),
            ],
            'string' => [
                'featureIdentifier' => new CsvIdentifierExists('feature'),
                'pokedexIdentifier' => new YamlIdentifierExists('pokedex'),
            ],
        ];
    }

}
