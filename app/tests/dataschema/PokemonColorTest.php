<?php

namespace App\Tests\dataschema;


use App\Tests\data\CsvParserTrait;

/**
 * Test Pokemon Color
 *
 * @group data
 * @group pokemon_color
 * @coversNothing
 */
class PokemonColorTest extends DataSchemaTestCase
{
    use CsvParserTrait;

    /**
     * Test data matches schema
     */
    public function testData(): void
    {
        $allData = $this->getIteratorForCsv('pokemon_color');
        $this->assertDataSchema('pokemon_color', $allData);
    }
}
