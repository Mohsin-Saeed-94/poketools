<?php

namespace App\Tests\dataschema;

use App\Tests\data\CsvParserTrait;


/**
 * Test Characteristic
 *
 * @group data
 * @group characteristic
 * @coversNothing
 */
class CharacteristicTest extends DataSchemaTestCase
{
    use CsvParserTrait;

    /**
     * Test data matches schema
     */
    public function testData(): void
    {
        $allData = $this->getIteratorForCsv('characteristic');
        self::assertDataSchema('characteristic', $allData);
    }
}
