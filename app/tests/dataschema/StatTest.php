<?php

namespace App\Tests\dataschema;


use App\Tests\dataschema\Filter\CsvIdentifierExists;
use App\Tests\Traits\CsvParserTrait;

/**
 * Test Stat
 *
 * @group data
 * @group stat
 * @coversNothing
 */
class StatTest extends DataSchemaTestCase
{

    use CsvParserTrait;

    /**
     * Test data matches schema
     *
     * @dataProvider dataProvider
     */
    public function testData(array $row): void
    {
        $this->assertDataSchema('stat', $row);
    }

    public function dataProvider()
    {
        return $this->buildCsvDataProvider('stat', 'identifier');
    }

    /**
     * @inheritDoc
     */
    protected function getFilters(): array
    {
        return [
            'string' => [
                'damageClassIdentifier' => new CsvIdentifierExists('move_damage_class'),
            ],
        ];
    }

}
