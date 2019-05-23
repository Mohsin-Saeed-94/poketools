<?php
/**
 * @file LocationTest.php
 */

namespace App\Tests\dataschema;

use App\Tests\data\DataFinderTrait;

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
    protected const DIR_DATA = self::BASE_DIR_SCHEMA.'/../data/location';

    /**
     * Test data structure
     */
    public function testData(): void
    {
        $allData = $this->getLocationsData();

        foreach ($allData as $identifier => $yaml) {
            $data = $this->parseYaml($yaml);
            self::assertDataSchema('location', $data, $identifier);
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

    /**
     * Test the version groups exist
     *
     * @depends testData
     */
    public function testVersionGroup(): void
    {
        $allData = $this->getLocationsData();

        foreach ($allData as $identifier => $yaml) {
            $data = $this->parseYaml($yaml);
            foreach (array_keys($data) as $versionGroup) {
                $versionGroupFilePath = sprintf(
                    '%s/%s.yaml',
                    realpath(self::DIR_DATA.'/../version_group'),
                    $versionGroup
                );
                self::assertFileExists(
                    $versionGroupFilePath,
                    sprintf('[%s] The version group "%s" does not exist.', $identifier, $versionGroup)
                );
            }
        }
    }

    /**
     * Test region exists
     *
     * @depends testData
     * @depends testVersionGroup
     */
    public function testRegion(): void
    {
        $allData = $this->getLocationsData();

        foreach ($allData as $identifier => $yaml) {
            $data = $this->parseYaml($yaml);
            foreach ($data as $versionGroupSlug => $versionGroupData) {
                $regionSlug = $versionGroupData['region'];
                $regionFilePath = sprintf('%s/%s.yaml', realpath(self::DIR_DATA.'/../region'), $regionSlug);
                self::assertFileExists(
                    $regionFilePath,
                    sprintf('[%s] [%s] region "%s" does not exist.', $identifier, $versionGroupSlug, $regionSlug)
                );
            }
        }
    }

    /**
     * Test that the "super" property refers to a real location.
     *
     * @depends testData
     * @depends testVersionGroup
     */
    public function testSuper(): void
    {
        $allData = $this->getLocationsData();

        foreach ($allData as $identifier => $yaml) {
            $data = $this->parseYaml($yaml);
            foreach ($data as $versionGroupSlug => $versionGroupData) {
                if (!isset($versionGroupData['super'])) {
                    continue;
                }

                $superLocationSlug = $versionGroupData['super'];
                $superLocationFilePath = sprintf('%s/%s.yaml', realpath(self::DIR_DATA), $superLocationSlug);
                self::assertFileExists(
                    $superLocationFilePath,
                    sprintf(
                        '[%s] [%s] super refers to "%s" which does not exist.',
                        $identifier,
                        $versionGroupSlug,
                        $superLocationSlug
                    )
                );
            }
        }
    }

    /**
     * Test that only one area is default.
     *
     * @depends testData
     * @depends testVersionGroup
     */
    public function testAreas(): void
    {
        $allData = $this->getLocationsData();

        foreach ($allData as $identifier => $yaml) {
            $data = $this->parseYaml($yaml);
            foreach ($data as $versionGroupSlug => $versionGroupData) {
                self::assertNotEmpty($versionGroupData['areas']);
                $defaults = 0;
                foreach ($versionGroupData['areas'] as $areaSlug => $areaData) {
                    if (isset($areaData['default']) && $areaData['default'] === true) {
                        $defaults++;
                    }
                }
                self::assertEquals(
                    1,
                    $defaults,
                    sprintf('[%s] [%s] Exactly one area must be set as default.', $identifier, $versionGroupSlug)
                );
            }
        }
    }

    /**
     * Test map descriptors
     *
     * @depends testData
     * @depends testVersionGroup
     */
    public function testMap(): void
    {
        $allData = $this->getLocationsData();

        foreach ($allData as $identifier => $yaml) {
            $data = $this->parseYaml($yaml);

            libxml_use_internal_errors(true);
            foreach ($data as $versionGroupSlug => $versionData) {
                if (!isset($versionData['map'])) {
                    continue;
                }

                $mapData = $versionData['map'];
                self::assertArrayHasKey('map', $mapData, 'Map not set');
                self::assertNotEmpty($mapData['map'], 'Map not set');
                self::assertArrayHasKey('overlay', $mapData, 'No overlay');
                self::assertNotEmpty($mapData['overlay'], 'Overlay is empty');
                $doc = simplexml_load_string('<svg>'.$mapData['overlay'].'</svg>');
                if ($doc === false) {
                    $errors = [];
                    foreach (libxml_get_errors() as $error) {
                        /** @var \LibXMLError $error */
                        $errorMessage = explode("\n", $mapData['overlay'])[$error->line - 1]."\n";
                        $errorMessage .= str_repeat('-', $error->column)."^\n";

                        switch ($error->level) {
                            case LIBXML_ERR_WARNING:
                                $errorMessage .= "Warning $error->code: ";
                                break;
                            case LIBXML_ERR_ERROR:
                                $errorMessage .= "Error $error->code: ";
                                break;
                            case LIBXML_ERR_FATAL:
                                $errorMessage .= "Fatal Error $error->code: ";
                                break;
                        }

                        $errorMessage .= trim($error->message).
                            "\n  Line: $error->line".
                            "\n  Column: $error->column";

                        if ($error->file) {
                            $errorMessage .= "\n  File: $error->file";
                        }

                        $errors[] = $errorMessage;
                    }
                    libxml_clear_errors();
                    self::assertNotEquals(false, $doc, "Overlay is not well formed:\n".implode("\n\n", $errors));
                }
            }
        }
    }
}
