<?php

namespace App\Tests\dataschema;


use App\Tests\data\CsvParserTrait;

/**
 * Test Move Damage Class
 *
 * @group data
 * @group move_damage_class
 * @coversNothing
 */
class MoveDamageClassTest extends DataSchemaTestCase
{
    use CsvParserTrait;

    /**
     * Test data matches schema
     */
    public function testData(): void
    {
        $allData = $this->getIteratorForCsv('move_damage_class');
        $this->assertDataSchema('move_damage_class', $allData);
    }
}
