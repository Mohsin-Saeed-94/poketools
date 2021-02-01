<?php

namespace App\Tests\dataschema;


use App\Tests\dataschema\Filter\YamlIdentifierExists;
use App\Tests\Traits\CsvParserTrait;

/**
 * Test Version
 *
 * @group data
 * @group version
 * @coversNothing
 */
class VersionTest extends DataSchemaTestCase
{

    use CsvParserTrait;

    /**
     * Test data matches schema
     *
     * @dataProvider dataProvider
     */
    public function testData(array $row): void
    {
        $this->assertDataSchema('version', $row);
    }

    public function dataProvider()
    {
        return $this->buildCsvDataProvider('version', 'identifier');
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
