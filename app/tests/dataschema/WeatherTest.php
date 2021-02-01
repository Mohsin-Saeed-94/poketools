<?php

namespace App\Tests\dataschema;


use App\Tests\Traits\CsvParserTrait;

/**
 * Test Weather
 *
 * @group data
 * @group weather
 * @coversNothing
 */
class WeatherTest extends DataSchemaTestCase
{

    use CsvParserTrait;

    /**
     * Test data matches schema
     *
     * @dataProvider dataProvider
     */
    public function testData(array $row): void
    {
        $this->assertDataSchema('weather', $row);
    }

    public function dataProvider()
    {
        return $this->buildCsvDataProvider('weather', 'identifier');
    }

}
