<?php

namespace App\Tests\dataschema;


use App\Tests\data\CsvParserTrait;
use App\Tests\dataschema\Filter\CsvIdentifierExists;

/**
 * Test Contest Type
 *
 * @group data
 * @group contest_type
 * @coversNothing
 */
class ContestTypeTest extends DataSchemaTestCase
{
    use CsvParserTrait;

    /**
     * Test data matches schema
     */
    public function testData(): void
    {
        $allData = $this->getIteratorForCsv('contest_type');
        $this->assertDataSchema('contest_type', $allData);
    }

    /**
     * @inheritDoc
     */
    protected function getFilters(): array
    {
        return [
            'string' => [
                'berryFlavorIdentifier' => new CsvIdentifierExists('berry_flavor'),
                'pokeblockColorIdentifier' => new CsvIdentifierExists('pokeblock_color'),
            ],
        ];
    }


}
