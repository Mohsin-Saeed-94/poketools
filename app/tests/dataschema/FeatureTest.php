<?php

namespace App\Tests\dataschema;


use App\Tests\data\CsvParserTrait;

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
     */
    public function testData(): void
    {
        $allData = $this->getIteratorForCsv('feature');
        self::assertDataSchema('feature', $allData);
    }
}
