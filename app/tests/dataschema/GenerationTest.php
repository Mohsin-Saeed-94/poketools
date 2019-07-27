<?php

namespace App\Tests\dataschema;


use App\Tests\data\CsvParserTrait;
use App\Tests\dataschema\Filter\YamlIdentifierExists;

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
     */
    public function testData(): void
    {
        $allData = $this->getIteratorForCsv('generation');
        $this->assertDataSchema('generation', $allData);
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
