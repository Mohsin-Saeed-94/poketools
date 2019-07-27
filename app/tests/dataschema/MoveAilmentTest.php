<?php

namespace App\Tests\dataschema;


use App\Tests\data\CsvParserTrait;

/**
 * Test Move Ailment
 *
 * @group data
 * @group move_ailment
 * @coversNothing
 */
class MoveAilmentTest extends DataSchemaTestCase
{
    use CsvParserTrait;

    /**
     * Test data matches schema
     */
    public function testData(): void
    {
        $allData = $this->getIteratorForCsv('move_ailment');
        $this->assertDataSchema('move_ailment', $allData);
    }
}
