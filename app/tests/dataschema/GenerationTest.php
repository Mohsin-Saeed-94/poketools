<?php

namespace App\Tests\dataschema;


use App\Tests\dataschema\Filter\YamlIdentifierExists;
use App\Tests\Traits\CsvParserTrait;

/**
 * Test Generation
 *
 * @group data
 * @group generation
 * @coversNothing
 */
class GenerationTest extends DataSchemaTestCase
{

    use CsvParserTrait;

    /**
     * Test data matches schema
     *
     * @dataProvider dataProvider
     */
    public function testData(array $row): void
    {
        $this->assertDataSchema('generation', $row);
    }

    public function dataProvider()
    {
        return $this->buildCsvDataProvider('generation', 'id');
    }

    /**
     * @inheritDoc
     */
    protected function getFilters(): array
    {
        return [
            'string' => [
                'regionIdentifier' => new YamlIdentifierExists('region'),
            ],
        ];
    }

}
