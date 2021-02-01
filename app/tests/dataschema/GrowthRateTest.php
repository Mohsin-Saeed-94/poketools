<?php

namespace App\Tests\dataschema;


use App\Tests\dataschema\Filter\ExpressionFilter;
use App\Tests\Traits\YamlParserTrait;

/**
 * Test Growth Rate
 *
 * @group data
 * @group growth_rate
 * @coversNothing
 */
class GrowthRateTest extends DataSchemaTestCase
{

    use YamlParserTrait;

    /**
     * Test data matches schema
     *
     * @dataProvider dataProvider
     */
    public function testData(array $data): void
    {
        $this->assertDataSchema('growth_rate', $data);
    }

    public function dataProvider()
    {
        return $this->buildYamlDataProvider('growth_rate');
    }

    /**
     * @inheritDoc
     */
    protected function getFilters(): array
    {
        return [
            'string' => [
                'expression' => new ExpressionFilter(),
            ],
        ];
    }

}
