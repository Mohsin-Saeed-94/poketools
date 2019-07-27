<?php

namespace App\Tests\dataschema;


use App\Tests\data\DataFinderTrait;

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
    protected const DIR_DATA = self::BASE_DIR_SCHEMA.'/../data/growth_rate';

    /**
     * Test data matches schema
     */
    public function testData(): void
    {
        $allData = $this->getData();
        foreach ($allData as $identifier => $yaml) {
            $data = $this->parseYaml($yaml);
            self::assertDataSchema('growth_rate', $data, $identifier);
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
}
