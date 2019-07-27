<?php

namespace App\Tests\dataschema;


use App\Tests\data\CsvParserTrait;

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
     */
    public function testData(): void
    {
        $allData = $this->getIteratorForCsv('pokeathlon_stat');
        $this->assertDataSchema('pokeathlon_stat', $allData);
    }
}
