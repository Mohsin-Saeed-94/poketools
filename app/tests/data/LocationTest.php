<?php
/**
 * @file LocationTest.php
 */

namespace App\Tests\data;

class LocationTest extends DataTestCase
{
    /**
     * Test data structure
     *
     * @dataProvider locationsDataProvider
     *
     * @param string $yaml
     */
    public function testData(string $yaml): void
    {
        $data = $this->parseYaml($yaml);
        self::assertDataSchema('location', $data);
    }

    /**
     * Test descriptions are valid Markdown
     *
     * @dataProvider locationsDataProvider
     *
     * @param string $yaml
     *
     * @throws \ReflectionException
     */
    public function testDescription(string $yaml): void
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
     * Test map descriptors
     *
     * @dataProvider locationsDataProvider
     *
     * @param string $yaml
     */
    public function testMap(string $yaml): void
    {
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

    /**
     * @return \Generator
     */
    public function locationsDataProvider(): \Generator
    {
        $finder = $this->getFinderForDirectory('location');
        $finder->name('*.yaml');

        return $this->buildDataProvider($finder);
    }
}
