<?php

namespace App\Tests\dataschema;


use App\Tests\data\CsvParserTrait;

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
     */
    public function testData(): void
    {
        $allData = $this->getIteratorForCsv('pal_park_area');
        $this->assertDataSchema('pal_park_area', $allData);
    }
}
