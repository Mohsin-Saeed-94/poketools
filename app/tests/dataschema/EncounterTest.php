<?php
/**
 * @file EncounterTest.php
 */

namespace App\Tests\dataschema;


use App\Entity\Embeddable\Range;
use App\Tests\data\CsvParserTrait;

/**
 * Test Encounter data
 *
 * @group data
 * @group encounter
 * @coversNothing
 */
class EncounterTest extends DataSchemaTestCase
{
    use CsvParserTrait;

    /**
     * Test data matches schema
     */
    public function testData(): void
    {
        $allData = $this->getIteratorForCsv('encounter');
        self::assertDataSchema('encounter', $allData);
    }

    /**
     * Test that IDs do not repeat
     *
     * @depends testData
     */
    public function testId(): void
    {
        $allData = $this->getIteratorForCsv('encounter');
        $ids = array_column($allData, 'id');
        self::assertEquals($ids, array_unique($ids), 'An ID value is repeated.');
    }

    /**
     * Test that version exists
     *
     * @depends testData
     * @depends testId
     */
    public function testVersion(): void
    {
        $allData = $this->getIteratorForCsv('encounter');
        $versions = array_column($allData, 'version', 'id');
        foreach (array_unique($versions) as $version) {
            $usedIn = array_keys($versions, $version, true);
            self::assertContains(
                $version,
                array_keys($this->getVersions()),
                sprintf('[%s] The version "%s" does not exist.', implode(', ', $usedIn), $version)
            );
        }
    }

    /**
     * Get a map of versions to version groups
     *
     * @return string[]
     */
    private function getVersions(): array
    {
        static $versions = null;
        if (!isset($versions)) {
            $versionData = $this->getIteratorForCsv('version');
            $versions = array_column($versionData, 'version_group', 'identifier');
        }

        return $versions;
    }

    /**
     * Test that the location and area exist
     *
     * @depends testData
     * @depends testId
     * @depends testVersion
     */
    public function testLocationAndArea(): void
    {
        $allData = $this->getIteratorForCsv('encounter');
        $locations = array_column($allData, 'location', 'id');

        // Check locations exist
        foreach (array_unique($locations) as $location) {
            $locationFilePath = sprintf('%s/%s.yaml', realpath(self::BASE_DIR_DATA.'/location'), $location);
            $usedIn = array_keys($locations, $location, true);
            self::assertFileExists(
                $locationFilePath,
                sprintf('[%s] The location "%s" does not exist.', implode(', ', $usedIn), $location)
            );
        }

        // Check location and areas are proper in the version
        foreach ($allData as $encounter) {
            $id = $encounter['id'];
            $location = $encounter['location'];
            $locationFilePath = sprintf('%s/%s.yaml', realpath(self::BASE_DIR_DATA.'/location'), $location);
            $area = $encounter['area'];
            $versionGroup = $this->getVersions()[$encounter['version']];
            $locationData = $this->getDataFromYaml($locationFilePath);

            self::assertArrayHasKey(
                $versionGroup,
                $locationData,
                sprintf(
                    '[%s] The location "%s" does not exist in the version group "%s".',
                    $id,
                    $location,
                    $versionGroup
                )
            );
            self::assertArrayHasKey(
                $area,
                $locationData[$versionGroup]['areas'],
                sprintf(
                    '[%s] The location "%s" does not have the area "%s" in the version group "%s".',
                    $id,
                    $location,
                    $area,
                    $versionGroup
                )
            );
        }
    }

    /**
     * Test that the Pokemon exists
     *
     * @depends testData
     * @depends testId
     * @depends testVersion
     */
    public function testPokemon(): void
    {
        $allData = $this->getIteratorForCsv('encounter');
        $allSpecies = array_column($allData, 'species', 'id');
        foreach (array_unique($allSpecies) as $species) {
            $speciesFilePath = sprintf('%s/%s.yaml', realpath(self::BASE_DIR_DATA.'/pokemon'), $species);
            $usedIn = array_keys($allSpecies, $species, true);
            self::assertFileExists(
                $speciesFilePath,
                sprintf('[%s] The species "%s" does not exist.', implode(', ', $usedIn), $species)
            );
        }

        // Check Pokemon belong to species in version
        foreach ($allData as $encounter) {
            $id = $encounter['id'];
            $species = $encounter['species'];
            $speciesFilePath = sprintf('%s/%s.yaml', realpath(self::BASE_DIR_DATA.'/pokemon'), $species);
            $pokemon = $encounter['pokemon'];
            $versionGroup = $this->getVersions()[$encounter['version']];

            $pokemonData = $this->getDataFromYaml($speciesFilePath);
            self::assertArrayHasKey(
                $versionGroup,
                $pokemonData,
                sprintf('[%s] The species "%s" does not exist in the version group "%s".', $id, $species, $versionGroup)
            );
            self::assertArrayHasKey(
                $pokemon,
                $pokemonData[$versionGroup]['pokemon'],
                sprintf(
                    '[%s] The species "%s" does not have the Pokemon "%s" in the version group "%s".',
                    $id,
                    $species,
                    $pokemon,
                    $versionGroup
                )
            );
        }
    }

    /**
     * Test encounter method exists
     *
     * @depends testData
     * @depends testId
     * @depends testVersion
     */
    public function testMethod(): void
    {
        $allData = $this->getIteratorForCsv('encounter');
        $methods = array_column($allData, 'method', 'id');
        foreach (array_unique($methods) as $method) {
            $usedIn = array_keys($methods, $method, true);
            self::assertContains(
                $method,
                $this->getMethods(),
                sprintf(
                    '[%s] The encounter method "%s" does not exist.',
                    implode(', ', $usedIn),
                    $method
                )
            );
        }
    }

    /**
     * Get a list of encounter methods.
     *
     * @return string[]
     */
    private function getMethods(): array
    {
        static $methods = null;
        if (!isset($methods)) {
            $methodData = $this->getIteratorForCsv('encounter_method');
            $methods = array_column($methodData, 'identifier');
        }

        return $methods;
    }

    /**
     * Test that the level is a valid range
     *
     * @depends testData
     * @depends testId
     */
    public function testLevel(): void
    {
        $allData = $this->getIteratorForCsv('encounter');
        $levels = array_column($allData, 'level', 'id');
        foreach (array_unique($levels) as $level) {
            $usedIn = array_keys($levels, $level, true);
            $range = Range::fromString($level);
            self::assertEquals(
                $level,
                (string)$range,
                sprintf('[%s] The level "%s" is not a valid range.', implode(', ', $usedIn), $level)
            );
            self::assertGreaterThanOrEqual(
                1,
                $range->getMin(),
                sprintf('[%s] Levels must be in the range 1-100.', implode(', ', $usedIn))
            );
            self::assertLessThanOrEqual(
                100,
                $range->getMax(),
                sprintf('[%s] Levels must be in the range 1-100.', implode(', ', $usedIn))
            );
        }
    }

    /**
     * Test that the conditions list is formatted properly and that they exist.
     *
     * @depends testData
     * @depends testId
     */
    public function testConditions(): void
    {
        $allData = $this->getIteratorForCsv('encounter');
        $allConditions = array_column($allData, 'conditions', 'id');

        foreach (array_unique($allConditions) as $conditionString) {
            if (empty($conditionString)) {
                continue;
            }
            $usedIn = array_keys($allConditions, $conditionString, true);

            $conditions = explode(',', $conditionString);
            $conditions = array_map('trim', $conditions);
            self::assertEquals(
                implode(',', $conditions),
                $conditionString,
                sprintf('[%s]The conditions list is not formatted properly.', implode(', ', $usedIn))
            );
            foreach ($conditions as $condition) {
                $conditionParts = explode('/', $condition);
                self::assertCount(
                    2,
                    $conditionParts,
                    sprintf('[%s] The condition "%s" is not formatted properly.', implode(', ', $usedIn), $condition)
                );
                [$conditionGroup, $conditionState] = $conditionParts;
                $conditionFilePath = sprintf(
                    '%s/%s.yaml',
                    realpath(self::BASE_DIR_DATA.'/encounter_condition'),
                    $conditionGroup
                );
                self::assertFileExists(
                    $conditionFilePath,
                    sprintf('[%s] The condition "%s" does not exist.', implode(', ', $usedIn), $conditionGroup)
                );
                $conditionData = $this->getDataFromYaml($conditionFilePath);
                $conditionState = substr(
                    $conditionState,
                    strpos($conditionState, $conditionGroup) + strlen($conditionGroup) + 1
                );
                self::assertArrayHasKey(
                    $conditionState,
                    $conditionData['states'],
                    sprintf(
                        '[%s] The condition "%s" does not have the state "%s".',
                        implode(', ', $usedIn),
                        $conditionGroup,
                        $conditionState
                    )
                );
            }
        }
    }
}
