<?php
/**
 * @file BattleStyleTest.php
 */

namespace App\Tests\dataschema;

use App\Tests\data\CsvParserTrait;

/**
 * Test Battle Styles
 *
 * @group data
 * @group battle_style
 * @coversNothing
 */
class BattleStyleTest extends DataSchemaTestCase
{
    use CsvParserTrait;

    /**
     * Test data matches schema
     */
    public function testData(): void
    {
        $allData = $this->getIteratorForCsv('battle_style');
        self::assertDataSchema('battle_style', $allData);
    }
}
