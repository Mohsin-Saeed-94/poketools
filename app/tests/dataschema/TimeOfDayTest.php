<?php

namespace App\Tests\dataschema;


use App\Tests\Traits\CsvParserTrait;

/**
 * Test Time of Day
 *
 * @group data
 * @group time_of_day
 * @coversNothing
 */
class TimeOfDayTest extends DataSchemaTestCase
{

    use CsvParserTrait;

    /**
     * Test data matches schema
     *
     * @dataProvider dataProvider
     */
    public function testData(array $row): void
    {
        $this->assertDataSchema('time_of_day', $row);
    }

    public function dataProvider()
    {
        return $this->buildCsvDataProvider(
            'time_of_day',
            [
                'generation',
                'identifier',
            ]
        );
    }

}
