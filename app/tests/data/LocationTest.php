<?php
/**
 * @file LocationTest.php
 */

namespace App\Tests\data;

/**
 * Test Location data
 *
 * @group data
 * @group location
 * @coversNothing
 */
class LocationTest extends DataTestCase
{
    use DataFinderTrait;

    /**
     * Test descriptions are valid Markdown
     */
    public function testDescription(): void
    {
        $allData = $this->getLocationsData();

        foreach ($allData as $identifier => $yaml) {
            $data = $this->parseYaml($yaml);

            foreach ($data as $versionGroupSlug => $versionData) {
                if (!isset($versionData['description'])) {
                    continue;
                }

                $versionGroup = $this->getVersionGroup($versionGroupSlug);
                foreach ($versionGroup->getVersions() as $version) {
                    $converter = $this->getMarkdownConverter($version->getSlug(), [$identifier, $versionGroupSlug]);
                    $converter->convertToHtml($versionData['description']);
                }
            }
        }
    }

    /**
     * @return \Generator
     */
    public function getLocationsData(): \Generator
    {
        $finder = $this->getFinderForDirectory('location');
        $finder->name('*.yaml');

        foreach ($finder as $fileInfo) {
            yield $fileInfo->getFilename() => $fileInfo->getContents();
        }
    }
}
