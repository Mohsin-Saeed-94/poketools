<?php

namespace App\Tests\dataschema;


use App\Tests\data\CsvParserTrait;

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
     */
    public function testData(): void
    {
        $allData = $this->getIteratorForCsv('weather');
        self::assertDataSchema('weather', $allData);
    }
}
