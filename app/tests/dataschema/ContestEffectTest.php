<?php

namespace App\Tests\dataschema;


use App\Tests\data\CsvParserTrait;

/**
 * Test Contest Effect
 *
 * @group data
 * @group contest_effect
 * @coversNothing
 */
class ContestEffectTest extends DataSchemaTestCase
{
    use CsvParserTrait;

    /**
     * Test data matches schema
     */
    public function testData(): void
    {
        $allData = $this->getIteratorForCsv('contest_effect');
        self::assertDataSchema('contest_effect', $allData);
    }
}
