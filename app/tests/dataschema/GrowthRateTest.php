<?php

namespace App\Tests\dataschema;


use App\Tests\data\DataFinderTrait;
use App\Tests\data\YamlParserTrait;
use App\Tests\dataschema\Filter\ExpressionFilter;

/**
 * Test Growth Rate
 *
 * @group data
 * @group growth_rate
 * @coversNothing
 */
class GrowthRateTest extends DataSchemaTestCase
{
    use DataFinderTrait;
    use YamlParserTrait;

    /**
     * Test data matches schema
     */
    public function testData(): void
    {
        $allData = $this->getData();
        foreach ($allData as $identifier => $yaml) {
            $data = $this->parseYaml($yaml);
            $this->assertDataSchema('growth_rate', $data, $identifier);
        }
    }

    /**
     * @return \Generator
     */
    public function getData(): \Generator
    {
        $finder = $this->getFinderForDirectory('growth_rate');
        $finder->name('*.yaml');

        foreach ($finder as $fileInfo) {
            yield $fileInfo->getFilename() => $fileInfo->getContents();
        }
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
