<?php

namespace App\Tests\dataschema;


use App\Tests\dataschema\Filter\CsvIdentifierExists;
use App\Tests\dataschema\Filter\EntityHasVersionGroup;
use App\Tests\dataschema\Filter\RangeFilter;
use App\Tests\dataschema\Filter\SingleDefault;
use App\Tests\dataschema\Filter\SpeciesPokemonCombination;
use App\Tests\dataschema\Filter\TypeInVersionGroup;
use App\Tests\dataschema\Filter\VersionGroupHasPokedex;
use App\Tests\dataschema\Filter\VersionInVersionGroup;
use App\Tests\dataschema\Filter\YamlIdentifierExists;
use App\Tests\Traits\YamlParserTrait;

/**
 * Test Pokemon
 *
 * @group data
 * @group pokemon
 * @coversNothing
 */
class PokemonTest extends DataSchemaTestCase
{

    use YamlParserTrait;

    /**
     * Test data matches schema
     *
     * @dataProvider dataProvider
     */
    public function testData(array $data): void
    {
        $this->assertDataSchema('pokemon', $data);
    }

    public function dataProvider()
    {
        return $this->buildYamlDataProvider('pokemon');
    }

    /**
     * @inheritDoc
     */
    protected function getFilters(): array
    {
        return [
            'object' => [
                'singleDefault' => new SingleDefault(),
            ],
            'string' => [
                'abilityIdentifier' => new YamlIdentifierExists('ability'),
                'abilityInVersionGroup' => new EntityHasVersionGroup('ability'),
                'eggGroupIdentifier' => new CsvIdentifierExists('egg_group'),
                'evolutionTriggerIdentifier' => new CsvIdentifierExists('evolution_trigger'),
                'genderIdentifier' => new CsvIdentifierExists('gender'),
                'growthRateIdentifier' => new YamlIdentifierExists('growth_rate'),
                'habitatIdentifier' => new CsvIdentifierExists('pokemon_habitat'),
                'itemIdentifier' => new YamlIdentifierExists('item'),
                'itemInVersionGroup' => new EntityHasVersionGroup('item'),
                'locationIdentifier' => new YamlIdentifierExists('location'),
                'locationInVersionGroup' => new EntityHasVersionGroup('location'),
                'moveIdentifier' => new YamlIdentifierExists('move'),
                'moveInVersionGroup' => new EntityHasVersionGroup('move'),
                'palParkAreaIdentifier' => new CsvIdentifierExists('pal_park_area'),
                'pokeathlonStatIdentifier' => new CsvIdentifierExists('pokeathlon_stat'),
                'pokedexIdentifier' => new YamlIdentifierExists('pokedex'),
                'pokemonColorIdentifier' => new CsvIdentifierExists('pokemon_color'),
                'pokemonShapeIdentifier' => new YamlIdentifierExists('pokemon_shape'),
                'pokemonShapeInVersionGroup' => new EntityHasVersionGroup('pokemon_shape'),
                'range' => new RangeFilter(),
                'speciesIdentifier' => new YamlIdentifierExists('pokemon'),
                'speciesInVersionGroup' => new EntityHasVersionGroup('pokemon'),
                'speciesPokemonCombination' => new SpeciesPokemonCombination(),
                'statIdentifier' => new CsvIdentifierExists('stat'),
                'timeOfDayIdentifier' => new CsvIdentifierExists('time_of_day'),
                'typeIdentifier' => new CsvIdentifierExists('type'),
                'typeInVersionGroup' => new TypeInVersionGroup(),
                'versionGroupHasPokedex' => new VersionGroupHasPokedex(),
                'versionGroupIdentifier' => new YamlIdentifierExists('version_group'),
                'versionIdentifier' => new CsvIdentifierExists('version'),
                'versionInVersionGroup' => new VersionInVersionGroup(),
                'weatherIdentifier' => new CsvIdentifierExists('weather'),
            ],
        ];
    }

}
