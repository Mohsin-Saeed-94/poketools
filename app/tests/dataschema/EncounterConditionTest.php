<?php

namespace App\Tests\dataschema;


use App\Tests\data\DataFinderTrait;

/**
 * Test Encounter Condition
 *
 * @group data
 * @group encounter_condition
 * @coversNothing
 */
class EncounterConditionTest extends DataSchemaTestCase
{
    use DataFinderTrait;
    protected const DIR_DATA = self::BASE_DIR_SCHEMA.'/../data/encounter_condition';

    /**
     * Test data matches schema
     */
    public function testData(): void
    {
        $allData = $this->getData();
        foreach ($allData as $identifier => $yaml) {
            $data = $this->parseYaml($yaml);
            self::assertDataSchema('encounter_condition', $data, $identifier);
        }
    }

    /**
     * @return \Generator
     */
    public function getData(): \Generator
    {
        $finder = $this->getFinderForDirectory('encounter_condition');
        $finder->name('*.yaml');

        foreach ($finder as $fileInfo) {
            yield $fileInfo->getFilename() => $fileInfo->getContents();
        }
    }
}
