<?php

namespace App\Tests\dataschema;


use App\Tests\data\CsvParserTrait;

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
     */
    public function testData(): void
    {
        // @todo Implement
        $allData = $this->getIteratorForCsv('pokemon_habitat');
        $this->assertDataSchema('pokemon_habitat', $allData);
    }
}
