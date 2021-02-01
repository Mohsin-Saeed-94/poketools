<?php

namespace App\Tests\dataschema;


use App\Tests\Traits\CsvParserTrait;

/**
 * Test Feature
 *
 * @group data
 * @group feature
 * @coversNothing
 */
class FeatureTest extends DataSchemaTestCase
{

    use CsvParserTrait;

    /**
     * Test data matches schema
     *
     * @dataProvider dataProvider
     */
    public function testData(array $row): void
    {
        $this->assertDataSchema('feature', $row);
    }

    public function dataProvider()
    {
        return $this->buildCsvDataProvider('feature', 'identifier');
    }

}
