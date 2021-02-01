<?php

namespace App\Tests\dataschema;


use App\Tests\Traits\CsvParserTrait;

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
     *
     * @dataProvider dataProvider
     */
    public function testData(array $row): void
    {
        $this->assertDataSchema('gender', $row);
    }

    public function dataProvider()
    {
        return $this->buildCsvDataProvider('gender', 'identifier');
    }

}
