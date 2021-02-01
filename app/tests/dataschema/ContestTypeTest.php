<?php

namespace App\Tests\dataschema;


use App\Tests\dataschema\Filter\CsvIdentifierExists;
use App\Tests\Traits\CsvParserTrait;

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
     *
     * @dataProvider dataProvider
     */
    public function testData(array $row): void
    {
        $this->assertDataSchema('contest_type', $row);
    }

    public function dataProvider()
    {
        return $this->buildCsvDataProvider('contest_type', 'identifier');
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
