<?php
/**
 * @file BerryFirmnessTest.php
 */

namespace App\Tests\dataschema;

use App\Tests\data\CsvParserTrait;

/**
 * Test Berry Firmness
 *
 * @group data
 * @group berry_firmness
 * @coversNothing
 */
class BerryFirmnessTest extends DataSchemaTestCase
{
    use CsvParserTrait;

    /**
     * Test data matches schema
     */
    public function testData(): void
    {
        $allData = $this->getIteratorForCsv('berry_firmness');
        $this->assertDataSchema('berry_firmness', $allData);
    }
}
