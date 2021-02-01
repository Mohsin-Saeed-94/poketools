<?php

namespace App\Tests\dataschema;


use App\Tests\Traits\CsvParserTrait;

/**
 * Test Pokeblock Color
 *
 * @group data
 * @group pokeblock_color
 * @coversNothing
 */
class PokeblockColorTest extends DataSchemaTestCase
{

    use CsvParserTrait;

    /**
     * Test data matches schema
     *
     * @dataProvider dataProvider
     */
    public function testData(array $row): void
    {
        $this->assertDataSchema('pokeblock_color', $row);
    }

    public function dataProvider()
    {
        return $this->buildCsvDataProvider('pokeblock_color', 'identifier');
    }

}
