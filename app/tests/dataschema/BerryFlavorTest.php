<?php
/**
 * @file BerryFlavorTest.php
 */

namespace App\Tests\dataschema;

use App\Tests\data\CsvParserTrait;

/**
 * Test Berry Flavor
 *
 * @group data
 * @group berry_flavor
 * @coversNothing
 */
class BerryFlavorTest extends DataSchemaTestCase
{
    use CsvParserTrait;

    /**
     * Test data matches schema
     */
    public function testData(): void
    {
        $allData = $this->getIteratorForCsv('berry_flavor');
        self::assertDataSchema('berry_flavor', $allData);
    }
}
