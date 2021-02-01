<?php

namespace App\Tests\dataschema;


use App\Tests\Traits\CsvParserTrait;

/**
 * Test Pokeathlon Stat
 *
 * @group data
 * @group pokeathlon_stat
 * @coversNothing
 */
class PokeathlonStatTest extends DataSchemaTestCase
{

    use CsvParserTrait;

    /**
     * Test data matches schema
     *
     * @dataProvider dataProvider
     */
    public function testData(array $row): void
    {
        $this->assertDataSchema('pokeathlon_stat', $row);
    }

    public function dataProvider()
    {
        return $this->buildCsvDataProvider('pokeathlon_stat', 'identifier');
    }

}
