<?php

namespace App\Tests\dataschema;

use App\Tests\Traits\CsvParserTrait;

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
     *
     * @dataProvider dataProvider
     */
    public function testData(array $row): void
    {
        $this->assertDataSchema('berry_flavor', $row);
    }

    public function dataProvider()
    {
        return $this->buildCsvDataProvider('berry_flavor', 'identifier');
    }

}
