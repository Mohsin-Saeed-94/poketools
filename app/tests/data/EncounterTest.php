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
     */
    public function testNote(): void
    {
        $allData = $this->getIteratorForCsv('encounter');
        $badNotes = [];
        foreach ($allData as $encounter) {
            if (empty($encounter['note'])) {
                continue;
            }

            $converter = $this->getMarkdownConverter($encounter['version'], [$encounter['id']], $badNotes);
            self::assertNotEmpty($converter->convertToHtml($encounter['note']));
        }
        self::assertEmpty($badNotes, "Some notes are bad:".implode("\n", $badNotes));
    }
}
