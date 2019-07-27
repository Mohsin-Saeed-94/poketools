<?php

namespace App\Tests\dataschema;


use App\Tests\data\CsvParserTrait;

/**
 * Test Gender
 *
 * @group data
 * @group gender
 * @coversNothing
 */
class GenderTest extends DataSchemaTestCase
{
    use CsvParserTrait;

    /**
     * Test data matches schema
     */
    public function testData(): void
    {
        $allData = $this->getIteratorForCsv('gender');
        $this->assertDataSchema('gender', $allData);
    }
}
