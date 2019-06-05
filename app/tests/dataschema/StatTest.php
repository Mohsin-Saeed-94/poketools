<?php

namespace App\Tests\dataschema;


use App\Tests\data\CsvParserTrait;

/**
 * Test Stat
 *
 * @group data
 * @group stat
 * @coversNothing
 */
class StatTest extends DataSchemaTestCase
{
    use CsvParserTrait;

    /**
     * Test data matches schema
     */
    public function testData(): void
    {
        $allData = $this->getIteratorForCsv('stat');
        self::assertDataSchema('stat', $allData);
    }
}
