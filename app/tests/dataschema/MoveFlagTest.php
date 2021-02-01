<?php

namespace App\Tests\dataschema;


use App\Tests\Traits\CsvParserTrait;

/**
 * Test Move Flag
 *
 * @group data
 * @group move_flag
 * @coversNothing
 */
class MoveFlagTest extends DataSchemaTestCase
{

    use CsvParserTrait;

    /**
     * Test data matches schema
     *
     * @dataProvider dataProvider
     */
    public function testData(array $data): void
    {
        $this->assertDataSchema('move_flag', $data);
    }

    public function dataProvider()
    {
        return $this->buildCsvDataProvider('move_flag', 'identifier');
    }

}
