<?php

namespace App\Tests\dataschema;


use App\Tests\data\CsvParserTrait;
use App\Tests\dataschema\Filter\EntityHasVersionGroup;
use App\Tests\dataschema\Filter\YamlIdentifierExists;

/**
 * Test Pokemon Move
 *
 * @group data
 * @group pokemon_move
 * @coversNothing
 */
class PokemonMoveTest extends DataSchemaTestCase
{
    use CsvParserTrait;

    /**
     * Test data matches schema
     */
    public function testData(): void
    {
        $allData = $this->getIteratorForCsv('pokemon_move');

        // This loop is needed to work around an unknown bug in the validator.  Allowing the validator to iterate
        // will cause the test to take literal hours instead of seconds.
        foreach ($allData as $i => $data) {
            $this->assertDataSchema('pokemon_move', [$data], $i);
        }
    }

    /**
     * Test for repetitions
     */
    public function testUnique(): void
    {
        $allData = $this->getIteratorForCsv('pokemon_move');

        // Serialize the map values for uniqueness check
        $strings = [];
        foreach ($allData as $i => $data) {
            $strings[$i] = serialize($data);
        }
        sort($strings);
        $uniqueStrings = array_unique($strings);
        $duplicates = array_diff($strings, $uniqueStrings);
        self::assertEmpty($duplicates, count($duplicates).' rows are duplicated');
    }

    /**
     * @inheritDoc
     */
    protected function getFilters(): array
    {
        return [
            'string' => [
                'speciesIdentifier' => new YamlIdentifierExists('pokemon'),
                'speciesInVersionGroup' => new EntityHasVersionGroup('pokemon'),
                'versionGroupIdentifier' => new YamlIdentifierExists('version_group'),
                'moveIdentifier' => new YamlIdentifierExists('move'),
                'moveInVersionGroup' => new EntityHasVersionGroup('move'),
                'learnMethodIdentifier' => new YamlIdentifierExists('move_learn_method'),
                'itemIdentifier' => new YamlIdentifierExists('item'),
                'itemInVersionGroup' => new EntityHasVersionGroup('item'),
            ],
        ];
    }
}
