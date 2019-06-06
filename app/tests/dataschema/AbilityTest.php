<?php

namespace App\Tests\dataschema;


use App\Tests\data\DataFinderTrait;

/**
 * Test Ability
 *
 * @group data
 * @group ability
 * @coversNothing
 */
class AbilityTest extends DataSchemaTestCase
{
    use DataFinderTrait;
    protected const DIR_DATA = self::BASE_DIR_SCHEMA.'/../data/ability';

    /**
     * Test data matches schema
     */
    public function testData(): void
    {
        $allData = $this->getData();
        foreach ($allData as $identifier => $yaml) {
            $data = $this->parseYaml($yaml);
            self::assertDataSchema('ability', $data, $identifier);
        }
    }

    /**
     * @return \Generator
     */
    public function getData(): \Generator
    {
        $finder = $this->getFinderForDirectory('ability');
        $finder->name('*.yaml');

        foreach ($finder as $fileInfo) {
            yield $fileInfo->getFilename() => $fileInfo->getContents();
        }
    }
}
