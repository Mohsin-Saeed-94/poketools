<?php

namespace App\Tests\dataschema;


use App\Tests\data\CsvParserTrait;

/**
 * Test Contest Type
 *
 * @group data
 * @group contest_type
 * @coversNothing
 */
class ContestTypeTest extends DataSchemaTestCase
{
    use CsvParserTrait;

    /**
     * Test data matches schema
     */
    public function testData(): void
    {
        $allData = $this->getIteratorForCsv('contest_type');
        self::assertDataSchema('contest_type', $allData);
    }
}
