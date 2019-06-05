<?php

namespace App\Tests\dataschema;


use App\Tests\data\CsvParserTrait;

/**
 * Test Type
 *
 * @group data
 * @group type
 * @coversNothing
 */
class TypeTest extends DataSchemaTestCase
{
    use CsvParserTrait;

    /**
     * Test data matches schema
     */
    public function testData(): void
    {
        $allData = $this->getIteratorForCsv('type');
        self::assertDataSchema('type', $allData);
    }
}
