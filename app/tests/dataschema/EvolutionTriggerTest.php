<?php

namespace App\Tests\dataschema;


use App\Tests\data\CsvParserTrait;

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
     */
    public function testData(): void
    {
        $allData = $this->getIteratorForCsv('evolution_trigger');
        $this->assertDataSchema('evolution_trigger', $allData);
    }
}
