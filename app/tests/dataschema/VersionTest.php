<?php

namespace App\Tests\dataschema;


use App\Tests\data\CsvParserTrait;

/**
 * Test Version
 *
 * @group data
 * @group version
 * @coversNothing
 */
class VersionTest extends DataSchemaTestCase
{
    use CsvParserTrait;

    /**
     * Test data matches schema
     */
    public function testData(): void
    {
        $allData = $this->getIteratorForCsv('version');
        self::assertDataSchema('version', $allData);
    }
}
