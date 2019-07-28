<?php

namespace App\Tests\dataschema;


use App\Tests\data\DataFinderTrait;
use App\Tests\data\YamlParserTrait;
use App\Tests\dataschema\Filter\CsvIdentifierExists;
use App\Tests\dataschema\Filter\EntityHasVersionGroup;
use App\Tests\dataschema\Filter\RangeFilter;
use App\Tests\dataschema\Filter\SingleDefault;
use App\Tests\dataschema\Filter\SpeciesPokemonCombination;
use App\Tests\dataschema\Filter\TypeInVersionGroup;
use App\Tests\dataschema\Filter\VersionInVersionGroup;
use App\Tests\dataschema\Filter\YamlIdentifierExists;

/**
 * Test Pokemon
 *
 * @group data
 * @group pokemon
 * @coversNothing
 */
class PokemonTest extends DataSchemaTestCase
{
    use DataFinderTrait;
    use YamlParserTrait;

    /**
     * Test data matches schema
     */
    public function testData(): void
    {
        $allData = $this->getData();
        foreach ($allData as $identifier => $yaml) {
            $data = $this->parseYaml($yaml);
            $this->assertDataSchema('pokemon', $data, $identifier);
        }
    }

    /**
     * @return \Generator
     */
    public function getData(): \Generator
    {
        $finder = $this->getFinderForDirectory('pokemon');
        $finder->name('*.yaml');

        foreach ($finder as $fileInfo) {
            yield $fileInfo->getFilename() => $fileInfo->getContents();
        }
    }

    /**
     * Test Pokemon has numbers for all Pokedexes in the version group
     *
     * @depends testData
     */
    public function testPokedex(): void
    {
        $pokedexes = $this->getPokedexes();
        $allData = $this->getData();
        foreach ($allData as $identifier => $yaml) {
            $data = $this->parseYaml($yaml);
            foreach ($data as $versionGroupSlug => $versionGroupData) {
                if (!in_array($versionGroupSlug, ['colosseum', 'xd'])) {
                    foreach ($versionGroupData['numbers'] as $pokedex => $number) {
                        self::assertContains(
                            $pokedex,
                            $pokedexes[$versionGroupSlug],
                            sprintf(
                                '[%s] [%s] The pokedex "%s" does not appear in this version group.',
                                $identifier,
                                $versionGroupSlug,
                                $pokedex
                            )
                        );
                    }
                }
            }
        }
    }

    /**
     * Get a map of version groups to Pokedexes they use
     *
     * @return array
     */
    private function getPokedexes(): array
    {
        $finder = $this->getFinderForDirectory('pokedex');
        $finder->name('*.yaml');

        $pokedexes = [];
        foreach ($finder as $fileInfo) {
            $identifier = $fileInfo->getBasename('.'.$fileInfo->getExtension());
            $data = $this->parseYaml($fileInfo->getContents());
            foreach ($data['version_groups'] as $versionGroup) {
                $pokedexes[$versionGroup][] = $identifier;
            }
        }
        unset($pokedexList);

        return $pokedexes;
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
                'range' => new RangeFilter(),
                'versionGroupIdentifier' => new YamlIdentifierExists('version_group'),
                'pokedexIdentifier' => new YamlIdentifierExists('pokedex'),
                'pokemonShapeIdentifier' => new YamlIdentifierExists('pokemon_shape'),
                'pokemonShapeInVersionGroup' => new EntityHasVersionGroup('pokemon_shape'),
                'pokemonColorIdentifier' => new CsvIdentifierExists('pokemon_color'),
                'habitatIdentifier' => new CsvIdentifierExists('pokemon_habitat'),
                'growthRateIdentifier' => new YamlIdentifierExists('growth_rate'),
                'palParkAreaIdentifier' => new CsvIdentifierExists('pal_park_area'),
                'typeIdentifier' => new CsvIdentifierExists('type'),
                'typeInVersionGroup' => new TypeInVersionGroup(),
                'eggGroupIdentifier' => new CsvIdentifierExists('egg_group'),
                'statIdentifier' => new CsvIdentifierExists('stat'),
                'evolutionTriggerIdentifier' => new CsvIdentifierExists('evolution_trigger'),
                'genderIdentifier' => new CsvIdentifierExists('gender'),
                'locationIdentifier' => new YamlIdentifierExists('location'),
                'locationInVersionGroup' => new EntityHasVersionGroup('location'),
                'timeOfDayIdentifier' => new CsvIdentifierExists('time_of_day'),
                'moveIdentifier' => new YamlIdentifierExists('move'),
                'moveInVersionGroup' => new EntityHasVersionGroup('move'),
                'speciesIdentifier' => new YamlIdentifierExists('pokemon'),
                'speciesInVersionGroup' => new EntityHasVersionGroup('pokemon'),
                'weatherIdentifier' => new CsvIdentifierExists('weather'),
                'abilityIdentifier' => new YamlIdentifierExists('ability'),
                'abilityInVersionGroup' => new EntityHasVersionGroup('ability'),
                'versionIdentifier' => new CsvIdentifierExists('version'),
                'versionInVersionGroup' => new VersionInVersionGroup(),
                'itemIdentifier' => new YamlIdentifierExists('item'),
                'itemInVersionGroup' => new EntityHasVersionGroup('item'),
                'speciesPokemonCombination' => new SpeciesPokemonCombination(),
                'pokeathlonStatIdentifier' => new CsvIdentifierExists('pokeathlon_stat'),
            ],
        ];
    }
}
