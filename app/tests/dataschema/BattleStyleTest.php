<?php

namespace App\Tests\dataschema;

use App\Tests\Traits\CsvParserTrait;

/**
 * Test Battle Styles
 *
 * @group data
 * @group battle_style
 * @coversNothing
 */
class BattleStyleTest extends DataSchemaTestCase
{

    use CsvParserTrait;

    /**
     * Test data matches schema
     *
     * @dataProvider dataProvider
     */
    public function testData(array $row): void
    {
        $this->assertDataSchema('battle_style', $row);
    }

    public function dataProvider()
    {
        return $this->buildCsvDataProvider('battle_style', 'identifier');
    }

}
