<?php
/**
 * @file LocationTest.php
 */

namespace App\Tests\dataschema;

use App\Tests\data\DataFinderTrait;
use App\Tests\data\YamlParserTrait;
use App\Tests\dataschema\Filter\EntityHasVersionGroup;
use App\Tests\dataschema\Filter\SingleDefault;
use App\Tests\dataschema\Filter\YamlIdentifierExists;

/**
 * Test Location data
 *
 * @group data
 * @group location
 * @coversNothing
 */
class LocationTest extends DataSchemaTestCase
{
    use DataFinderTrait;
    use YamlParserTrait;
    protected const DIR_DATA = self::BASE_DIR_DATA.'/location';

    /**
     * Test data matches schema
     */
    public function testData(): void
    {
        $allData = $this->getData();

        foreach ($allData as $identifier => $yaml) {
            $data = $this->parseYaml($yaml);
            $this->assertDataSchema('location', $data, $identifier);
        }
    }

    /**
     * @return \Generator
     */
    public function getData(): \Generator
    {
        $finder = $this->getFinderForDirectory('location');
        $finder->name('*.yaml');

        foreach ($finder as $fileInfo) {
            yield $fileInfo->getFilename() => $fileInfo->getContents();
        }
    }

    /**
     * Test map descriptors
     *
     * @depends testData
     */
    public function testMap(): void
    {
        $allData = $this->getData();

        foreach ($allData as $identifier => $yaml) {
            $data = $this->parseYaml($yaml);

            foreach ($data as $versionGroupSlug => $versionGroupData) {
                if (!isset($versionGroupData['map'])) {
                    continue;
                }

                $mapData = $versionGroupData['map'];
                self::assertArrayHasKey(
                    'map',
                    $mapData,
                    sprintf('[%s] [%s] Map not set', $identifier, $versionGroupSlug)
                );
                $region = $versionGroupData['region'];
                $regionFilePath = sprintf(
                    '%s/%s.yaml',
                    realpath(self::DIR_DATA.'/../region'),
                    $region
                );
                $regionData = $this->getDataFromYaml($regionFilePath);
                $map = $mapData['map'];
                self::assertArrayHasKey(
                    $map,
                    $regionData[$versionGroupSlug]['maps'],
                    sprintf(
                        '[%s] [%s] Map "%s" does not exist in region "%s" in version group "%s".',
                        $identifier,
                        $versionGroupSlug,
                        $map,
                        $region,
                        $versionGroupSlug
                    )
                );
            }
        }
    }

    /**
     * @inheritDoc
     */
    protected function getFilters(): array
    {
        return [
            'object' => [
                'singleDefault' => new SingleDefault(),
            ],
            'string' => [
                'versionGroupIdentifier' => new YamlIdentifierExists('version_group'),
                'regionIdentifier' => new YamlIdentifierExists('region'),
                'regionInVersionGroup' => new EntityHasVersionGroup('region'),
                'locationIdentifier' => new YamlIdentifierExists('location'),
                'locationInVersionGroup' => new EntityHasVersionGroup('location'),
            ],
        ];
    }
}
