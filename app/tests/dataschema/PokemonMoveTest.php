<?php

namespace App\Tests\dataschema;


use App\Tests\dataschema\Filter\EntityHasVersionGroup;
use App\Tests\dataschema\Filter\YamlIdentifierExists;
use App\Tests\Traits\CsvParserTrait;

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
     *
     * @dataProvider dataProvider
     */
    public function testData(array $row): void
    {
        $this->assertDataSchema('pokemon_move', $row);
    }

    public function dataProvider()
    {
        return $this->buildCsvDataProvider('pokemon_move');
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
