<?php

namespace App\Tests\dataschema;


use App\Tests\data\CsvParserTrait;

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
     */
    public function testData(): void
    {
        $allData = $this->getIteratorForCsv('egg_group');
        self::assertDataSchema('egg_group', $allData);
    }
}
