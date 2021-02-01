<?php

namespace App\Tests\dataschema;


use App\Tests\Traits\CsvParserTrait;

/**
 * Test Move Ailment
 *
 * @group data
 * @group move_ailment
 * @coversNothing
 */
class MoveAilmentTest extends DataSchemaTestCase
{

    use CsvParserTrait;

    /**
     * Test data matches schema
     *
     * @dataProvider dataProvider
     */
    public function testData(array $row): void
    {
        $this->assertDataSchema('move_ailment', $row);
    }

    public function dataProvider()
    {
        return $this->buildCsvDataProvider('move_ailment', 'identifier');
    }

}
