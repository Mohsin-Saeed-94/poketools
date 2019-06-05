<?php

namespace App\Tests\dataschema;


use App\Tests\data\CsvParserTrait;

/**
 * Test Super Contest Effect
 *
 * @group data
 * @group super_contest_effect
 * @coversNothing
 */
class SuperContestEffectTest extends DataSchemaTestCase
{
    use CsvParserTrait;

    /**
     * Test data matches schema
     */
    public function testData(): void
    {
        $allData = $this->getIteratorForCsv('super_contest_effect');
        self::assertDataSchema('super_contest_effect', $allData);
    }
}
