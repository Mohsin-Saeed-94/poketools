<?php

namespace App\Tests\dataschema;


use App\Tests\data\DataFinderTrait;
use App\Tests\data\YamlParserTrait;
use App\Tests\dataschema\Filter\CsvIdentifierExists;
use App\Tests\dataschema\Filter\EntityHasVersionGroup;
use App\Tests\dataschema\Filter\RangeFilter;
use App\Tests\dataschema\Filter\TypeInVersionGroup;
use App\Tests\dataschema\Filter\YamlIdentifierExists;

/**
 * Test Move
 *
 * @group data
 * @group move
 * @coversNothing
 */
class MoveTest extends DataSchemaTestCase
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
            $this->assertDataSchema('move', $data, $identifier);
        }
    }

    /**
     * @return \Generator
     */
    public function getData(): \Generator
    {
        $finder = $this->getFinderForDirectory('move');
        $finder->name('*.yaml');

        foreach ($finder as $fileInfo) {
            yield $fileInfo->getFilename() => $fileInfo->getContents();
        }
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
