<?php

namespace App\Tests\dataschema;


use App\Tests\Traits\CsvParserTrait;

/**
 * Test Encounter Method
 *
 * @group data
 * @group encounter_method
 * @coversNothing
 */
class EncounterMethodTest extends DataSchemaTestCase
{

    use CsvParserTrait;

    /**
     * Test data matches schema
     *
     * @dataProvider dataProvider
     */
    public function testData(array $row): void
    {
        $this->assertDataSchema('encounter_method', $row);
    }

    public function dataProvider()
    {
        return $this->buildCsvDataProvider('encounter_method', 'identifier');
    }

}
