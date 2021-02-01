<?php

namespace App\Tests\dataschema;


use App\Tests\Traits\CsvParserTrait;

/**
 * Test Evolution Trigger
 *
 * @group data
 * @group evolution_trigger
 * @coversNothing
 */
class EvolutionTriggerTest extends DataSchemaTestCase
{

    use CsvParserTrait;

    /**
     * Test data matches schema
     *
     * @dataProvider dataProvider
     */
    public function testData(array $row): void
    {
        $this->assertDataSchema('evolution_trigger', $row);
    }

    public function dataProvider()
    {
        return $this->buildCsvDataProvider('evolution_trigger', 'identifier');
    }

}
