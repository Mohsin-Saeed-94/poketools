<?php

namespace App\Tests\dataschema;


use App\Tests\data\CsvParserTrait;

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
     */
    public function testData(): void
    {
        $allData = $this->getIteratorForCsv('pokeblock_color');
        $this->assertDataSchema('pokeblock_color', $allData);
    }
}
