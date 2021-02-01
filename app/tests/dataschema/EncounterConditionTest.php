<?php

namespace App\Tests\dataschema;


use App\Tests\dataschema\Filter\SingleDefault;
use App\Tests\Traits\YamlParserTrait;

/**
 * Test Encounter Condition
 *
 * @group data
 * @group encounter_condition
 * @coversNothing
 */
class EncounterConditionTest extends DataSchemaTestCase
{

    use YamlParserTrait;

    /**
     * Test data matches schema
     *
     * @dataProvider dataProvider
     */
    public function testData(array $data): void
    {
        $this->assertDataSchema('encounter_condition', $data);
    }

    public function dataProvider()
    {
        return $this->buildYamlDataProvider('encounter_condition');
    }

    /**
     * @inheritDoc
     */
    protected function getFilters(): array
    {
        return [
            'object' => [
                'singleDefault' => new SingleDefault(true),
            ],
        ];
    }

}
