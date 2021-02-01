<?php

namespace App\Tests\dataschema;


use App\Tests\dataschema\Filter\YamlIdentifierExists;
use App\Tests\Traits\YamlParserTrait;

/**
 * Test Move Learn Method
 *
 * @group data
 * @group move_learn_method
 * @coversNothing
 */
class MoveLearnMethodTest extends DataSchemaTestCase
{

    use YamlParserTrait;

    /**
     * Test data matches schema
     *
     * @dataProvider dataProvider
     */
    public function testData(array $data): void
    {
        $this->assertDataSchema('move_learn_method', $data);
    }

    public function dataProvider()
    {
        return $this->buildYamlDataProvider('move_learn_method');
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
