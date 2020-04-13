<?php

namespace App\Tests\data;

/**
 * Test Pokemon data
 *
 * @group data
 * @group pokemon
 * @coversNothing
 */
class PokemonTest extends DataTestCase
{
    use DataFinderTrait;
    use YamlParserTrait;

    /**
     * Test descriptions are valid Markdown
     */
    public function testFormNotes(): void
    {
        $allData = $this->getPokemonData();

        $invalidDescriptions = [];
        foreach ($allData as $identifier => $yaml) {
            $data = $this->parseYaml($yaml);

            foreach ($data as $versionGroupSlug => $versionData) {
                if (!isset($versionData['forms_note']) || !$versionData['forms_note']) {
                    continue;
                }
                $versionGroup = $this->getVersionGroup($versionGroupSlug);
                foreach ($versionGroup->getVersions() as $version) {
                    $converter = $this->getMarkdownConverter(
                        $version->getSlug(),
                        [$identifier, $versionGroupSlug],
                        $invalidDescriptions
                    );
                    self::assertNotEmpty($converter->convertToHtml($versionData['forms_note']));
                }
            }
        }

        self::assertEmpty($invalidDescriptions, "Some form notes are invalid:\n".implode("\n", $invalidDescriptions));
    }

    /**
     * @return \Generator
     */
    public function getPokemonData(): \Generator
    {
        $finder = $this->getFinderForDirectory('item');
        $finder->name('*.yaml');

        foreach ($finder as $fileInfo) {
            yield $fileInfo->getFilename() => $fileInfo->getContents();
        }
    }
}
