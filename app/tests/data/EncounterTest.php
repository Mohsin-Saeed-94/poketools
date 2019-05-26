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
        $hasNotes = false;
        foreach ($allData as $encounter) {
            if (empty($encounter['note'])) {
                continue;
            }
            $hasNotes = true;

            $converter = $this->getMarkdownConverter($encounter['version']);
            self::assertNotEmpty($converter->convertToHtml($encounter['note']));
        }
        if (!$hasNotes) {
            $this->markTestSkipped('No encounters have a note.');
        }
    }
}
