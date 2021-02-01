<?php

namespace App\Tests\dataschema;


use App\Tests\Traits\CsvParserTrait;

/**
 * Test Pal Park Area
 *
 * @group data
 * @group pal_park_area
 * @coversNothing
 */
class PalParkAreaTest extends DataSchemaTestCase
{

    use CsvParserTrait;

    /**
     * Test data matches schema
     *
     * @dataProvider dataProvider
     */
    public function testData(array $row): void
    {
        $this->assertDataSchema('pal_park_area', $row);
    }

    public function dataProvider()
    {
        return $this->buildCsvDataProvider('pal_park_area', 'identifier');
    }

}
