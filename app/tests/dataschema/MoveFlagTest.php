<?php

namespace App\Tests\dataschema;


use App\Tests\data\CsvParserTrait;

/**
 * Test Move Flag
 *
 * @group data
 * @group move_flag
 * @coversNothing
 */
class MoveFlagTest extends DataSchemaTestCase
{
    use CsvParserTrait;

    /**
     * Test data matches schema
     */
    public function testData(): void
    {
        $allData = $this->getIteratorForCsv('move_flag');
        $this->assertDataSchema('move_flag', $allData);
    }
}
