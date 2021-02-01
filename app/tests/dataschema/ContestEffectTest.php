<?php

namespace App\Tests\dataschema;


use App\Tests\dataschema\Filter\YamlIdentifierExists;
use App\Tests\Traits\YamlParserTrait;

/**
 * Test Contest Effect
 *
 * @group data
 * @group contest_effect
 * @coversNothing
 */
class ContestEffectTest extends DataSchemaTestCase
{

    use YamlParserTrait;

    /**
     * Test data matches schema
     *
     * @dataProvider dataProvider
     */
    public function testData(array $data): void
    {
        $this->assertDataSchema('contest_effect', $data);
    }

    public function dataProvider()
    {
        return $this->buildYamlDataProvider('contest_effect');
    }

    /**
     * @inheritDoc
     */
    protected function getFilters(): array
    {
        return [
            'string' => [
                'versionGroupIdentifier' => new YamlIdentifierExists('version_group'),
            ],
        ];
    }

}
