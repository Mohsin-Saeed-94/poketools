<?php

namespace App\Tests\dataschema;


use App\Tests\data\CsvParserTrait;

/**
 * Test Generation
 *
 * @group data
 * @group generation
 * @coversNothing
 */
class GenerationTest extends DataSchemaTestCase
{
    use CsvParserTrait;

    /**
     * Test data matches schema
     */
    public function testData(): void
    {
        $allData = $this->getIteratorForCsv('generation');
        self::assertDataSchema('generation', $allData);
    }
}
