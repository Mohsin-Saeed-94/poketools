<?php

namespace App\Tests\dataschema;

use App\Tests\Traits\CsvParserTrait;

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
     *
     * @dataProvider dataProvider
     */
    public function testData(array $row): void
    {
        $this->assertDataSchema('berry_firmness', $row);
    }

    public function dataProvider()
    {
        return $this->buildCsvDataProvider('berry_firmness', 'identifier');
    }

}
