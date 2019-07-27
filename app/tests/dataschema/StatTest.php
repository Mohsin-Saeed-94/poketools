<?php

namespace App\Tests\dataschema;


use App\Tests\data\CsvParserTrait;
use App\Tests\dataschema\Filter\CsvIdentifierExists;

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
     */
    public function testData(): void
    {
        $allData = $this->getIteratorForCsv('stat');
        $this->assertDataSchema('stat', $allData);
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
