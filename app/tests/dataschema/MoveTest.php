<?php

namespace App\Tests\dataschema;


use App\Tests\dataschema\Filter\CsvIdentifierExists;
use App\Tests\dataschema\Filter\EntityHasVersionGroup;
use App\Tests\dataschema\Filter\RangeFilter;
use App\Tests\dataschema\Filter\TypeInVersionGroup;
use App\Tests\dataschema\Filter\YamlIdentifierExists;
use App\Tests\Traits\YamlParserTrait;

/**
 * Test Move
 *
 * @group data
 * @group move
 * @coversNothing
 */
class MoveTest extends DataSchemaTestCase
{

    use YamlParserTrait;

    /**
     * Test data matches schema
     *
     * @dataProvider dataProvider
     */
    public function testData(array $data): void
    {
        $this->assertDataSchema('move', $data);
    }

    public function dataProvider()
    {
        return $this->buildYamlDataProvider('move');
    }

    /**
     * @inheritDoc
     */
    protected function getFilters(): array
    {
        return [
            'string' => [
                'versionGroupIdentifier' => new YamlIdentifierExists('version_group'),
                'ailmentIdentifier' => new CsvIdentifierExists('move_ailment'),
                'moveFlagIdentifier' => new CsvIdentifierExists('move_flag'),
                'moveCategoryIdentifier' => new CsvIdentifierExists('move_category'),
                'range' => new RangeFilter(),
                'statIdentifier' => new CsvIdentifierExists('stat'),
                'typeIdentifier' => new CsvIdentifierExists('type'),
                'typeInVersionGroup' => new TypeInVersionGroup(),
                'moveTargetIdentifier' => new CsvIdentifierExists('move_target'),
                'moveDamageClassIdentifier' => new CsvIdentifierExists('move_damage_class'),
                'contestTypeIdentifier' => new CsvIdentifierExists('contest_type'),
                'moveIdentifier' => new YamlIdentifierExists('move'),
                'moveInVersionGroup' => new EntityHasVersionGroup('move'),
            ],
            'integer' => [
                'moveEffectId' => new YamlIdentifierExists('move_effect'),
                'moveEffectInVersionGroup' => new EntityHasVersionGroup('move_effect'),
                'contestEffectId' => new YamlIdentifierExists('contest_effect'),
                'superContestEffectId' => new CsvIdentifierExists('super_contest_effect', 'id'),
            ],
        ];
    }

}
