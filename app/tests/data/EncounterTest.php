<?php
/**
 * @file EncounterTest.php
 */

namespace App\Tests\data;

/**
 * Test Encounter data
 *
 * @group data
 * @group encounter
 * @coversNothing
 */
class EncounterTest extends DataTestCase
{
    use CsvParserTrait;

    /**
     * Test that the note is valid Markdown.
     *
     * @dataProvider encountersDataProvider
     */
    public function testNote(array $encounter): void
    {
        if (empty($encounter['note'])) {
            self::markTestSkipped('Encounter has no note.');
            return;
        }

        $converter = $this->getMarkdownConverter($encounter['version']);
        self::assertNotEmpty($converter->convertToHtml($encounter['note']));

    }

    /**
     * @return \Generator
     */
    public function encountersDataProvider(): \Generator
    {
        return $this->buildArrayDataProvider($this->getIteratorForCsv('encounter'), ['id']);
    }
}
