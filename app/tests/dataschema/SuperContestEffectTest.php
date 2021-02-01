<?php

namespace App\Tests\dataschema;


use App\Tests\Traits\CsvParserTrait;

/**
 * Test Super Contest Effect
 *
 * @group data
 * @group super_contest_effect
 * @coversNothing
 */
class SuperContestEffectTest extends DataSchemaTestCase
{
    use CsvParserTrait;

    /**
     * Test data matches schema
     *
     * @dataProvider dataProvider
     */
    public function testData(array $data): void
    {

        $this->assertDataSchema('super_contest_effect', $data);
    }

    public function dataProvider()
    {
        return $this->buildCsvDataProvider('super_contest_effect', 'id');
    }

}
