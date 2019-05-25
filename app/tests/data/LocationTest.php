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
     *
     * @dataProvider locationsDataProvider
     */
    public function testDescription($yaml): void
    {
        $data = $this->parseYaml($yaml);

        foreach ($data as $versionGroupSlug => $versionData) {
            if (!isset($versionData['description'])) {
                continue;
            }

            $versionGroup = $this->getVersionGroup($versionGroupSlug);
            foreach ($versionGroup->getVersions() as $version) {
                $converter = $this->getMarkdownConverter($version->getSlug());
                $converter->convertToHtml($versionData['description']);
            }
        }
    }

    /**
     * @return \Generator
     */
    public function locationsDataProvider(): \Generator
    {
        $finder = $this->getFinderForDirectory('location');
        $finder->name('*.yaml');

        return $this->buildFinderDataProvider($finder);
    }
}
