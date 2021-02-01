<?php

namespace App\Tests\dataschema;


use App\Tests\dataschema\Filter\CsvIdentifierExists;
use App\Tests\Traits\CsvParserTrait;

/**
 * Test Type
 *
 * @group data
 * @group type
 * @coversNothing
 */
class TypeTest extends DataSchemaTestCase
{

    use CsvParserTrait;

    /**
     * Test data matches schema
     *
     * @dataProvider dataProvider
     */
    public function testData(array $row): void
    {
        $this->assertDataSchema('type', $row);
    }

    public function dataProvider()
    {
        return $this->buildCsvDataProvider('type', 'identifier');
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
