<?php

namespace App\Tests\dataschema;


use App\Tests\data\CsvParserTrait;

/**
 * Test Pokemon Move
 *
 * @group data
 * @group pokemon_move
 * @coversNothing
 */
class PokemonMoveTest extends DataSchemaTestCase
{
    use CsvParserTrait;

    /**
     * Test data matches schema
     */
    public function testData(): void
    {
        $allData = $this->getIteratorForCsv('pokemon_move');
        self::assertDataSchema('pokemon_move', $allData);
    }
}
