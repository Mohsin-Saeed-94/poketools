<?php

namespace App\Tests\dataschema;


use App\Tests\data\CsvParserTrait;

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
     */
    public function testData(): void
    {
        $allData = $this->getIteratorForCsv('encounter_method');
        self::assertDataSchema('encounter_method', $allData);
    }
}
