<?php

namespace App\Tests\dataschema;


use App\Tests\data\CsvParserTrait;

/**
 * Test Move Target
 *
 * @group data
 * @group move_target
 * @coversNothing
 */
class MoveTargetTest extends DataSchemaTestCase
{
    use CsvParserTrait;

    /**
     * Test data matches schema
     */
    public function testData(): void
    {
        $allData = $this->getIteratorForCsv('move_target');
        self::assertDataSchema('move_target', $allData);
    }
}
