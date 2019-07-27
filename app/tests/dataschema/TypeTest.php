<?php

namespace App\Tests\dataschema;


use App\Tests\data\CsvParserTrait;
use App\Tests\dataschema\Filter\CsvIdentifierExists;

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
     */
    public function testData(): void
    {
        $allData = $this->getIteratorForCsv('type');
        $this->assertDataSchema('type', $allData);
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
