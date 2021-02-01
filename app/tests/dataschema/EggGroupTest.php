<?php

namespace App\Tests\dataschema;


use App\Tests\Traits\CsvParserTrait;

/**
 * Test Egg Group
 *
 * @group data
 * @group egg_group
 * @coversNothing
 */
class EggGroupTest extends DataSchemaTestCase
{

    use CsvParserTrait;

    /**
     * Test data matches schema
     *
     * @dataProvider dataProvider
     */
    public function testData(array $row): void
    {
        $this->assertDataSchema('egg_group', $row);
    }

    public function dataProvider()
    {
        return $this->buildCsvDataProvider('egg_group', 'identifier');
    }

}
