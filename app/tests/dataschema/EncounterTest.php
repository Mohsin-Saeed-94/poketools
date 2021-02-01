<?php
/**
 * @file EncounterTest.php
 */

namespace App\Tests\dataschema;


use App\Tests\dataschema\Filter\CsvIdentifierExists;
use App\Tests\dataschema\Filter\EncounterConditionList;
use App\Tests\dataschema\Filter\EntityHasVersionGroup;
use App\Tests\dataschema\Filter\LocationHasArea;
use App\Tests\dataschema\Filter\RangeFilter;
use App\Tests\dataschema\Filter\SpeciesHasPokemon;
use App\Tests\dataschema\Filter\YamlIdentifierExists;
use App\Tests\Traits\CsvParserTrait;
use App\Tests\Traits\YamlParserTrait;

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
    use YamlParserTrait;

    /**
     * Test data matches schema
     *
     * @dataProvider dataProvider
     */
    public function testData(array $row): void
    {
        $this->assertDataSchema('encounter', $row);
    }

    public function dataProvider()
    {
        return $this->buildCsvDataProvider('encounter', 'id');
    }

    /**
     * @inheritDoc
     */
    protected function getFilters(): array
    {
        return [
            'string' => [
                'encounterConditionList' => new EncounterConditionList(),
                'encounterMethodIdentifier' => new CsvIdentifierExists('encounter_method'),
                'locationHasArea' => new LocationHasArea(),
                'locationIdentifier' => new YamlIdentifierExists('location'),
                'locationInVersionGroup' => new EntityHasVersionGroup('location'),
                'range' => new RangeFilter(),
                'speciesHasPokemon' => new SpeciesHasPokemon(),
                'speciesIdentifier' => new YamlIdentifierExists('pokemon'),
                'speciesInVersionGroup' => new EntityHasVersionGroup('pokemon'),
                'versionIdentifier' => new CsvIdentifierExists('version'),
            ],
        ];
    }

}
