<?php

namespace App\Tests\dataschema;


use App\Tests\Traits\CsvParserTrait;

/**
 * Test Move Target
 *
 * @group data
 * @group move_target
 * @coversNothing
 */
class MoveTargetTest extends DataSchemaTestCase
{

    use CsvParserTrait;

    /**
     * Test data matches schema
     *
     * @dataProvider dataProvider
     */
    public function testData(array $row): void
    {
        $this->assertDataSchema('move_target', $row);
    }

    public function dataProvider()
    {
        return $this->buildCsvDataProvider('move_target', 'identifier');
    }

}
