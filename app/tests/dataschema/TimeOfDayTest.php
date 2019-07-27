<?php

namespace App\Tests\dataschema;


use App\Tests\data\CsvParserTrait;

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
     */
    public function testData(): void
    {
        $allData = $this->getIteratorForCsv('time_of_day');
        $this->assertDataSchema('time_of_day', $allData);
    }
}
