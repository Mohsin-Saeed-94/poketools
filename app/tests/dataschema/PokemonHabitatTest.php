<?php

namespace App\Tests\dataschema;


use App\Tests\Traits\CsvParserTrait;

/**
 * Test Pokemon Habitat
 *
 * @group data
 * @group pokemon_habitat
 * @coversNothing
 */
class PokemonHabitatTest extends DataSchemaTestCase
{

    use CsvParserTrait;

    /**
     * Test data matches schema
     *
     * @dataProvider dataProvider
     */
    public function testData(array $row): void
    {
        $this->assertDataSchema('pokemon_habitat', $row);
    }

    public function dataProvider()
    {
        return $this->buildCsvDataProvider('pokemon_habitat', 'identifier');
    }

}
