<?php

namespace App\Tests\dataschema;


use App\Tests\data\DataFinderTrait;

/**
 * Test Item
 *
 * @group data
 * @group item
 * @coversNothing
 */
class ItemTest extends DataSchemaTestCase
{
    use DataFinderTrait;
    protected const DIR_DATA = self::BASE_DIR_SCHEMA.'/../data/item';

    /**
     * Test data matches schema
     */
    public function testData(): void
    {
        $allData = $this->getData();
        foreach ($allData as $identifier => $yaml) {
            $data = $this->parseYaml($yaml);
            self::assertDataSchema('item', $data, $identifier);
        }
    }

    /**
     * @return \Generator
     */
    public function getData(): \Generator
    {
        $finder = $this->getFinderForDirectory('item');
        $finder->name('*.yaml');

        foreach ($finder as $fileInfo) {
            yield $fileInfo->getFilename() => $fileInfo->getContents();
        }
    }
}
