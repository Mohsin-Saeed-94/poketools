<?php

namespace App\Tests\dataschema;


use App\Tests\data\DataFinderTrait;
use App\Tests\data\YamlParserTrait;
use App\Tests\dataschema\Filter\CsvIdentifierExists;
use App\Tests\dataschema\Filter\YamlIdentifierExists;

/**
 * Test Type Chart
 *
 * @group data
 * @group type_chart
 * @coversNothing
 */
class TypeChartTest extends DataSchemaTestCase
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
            $this->assertDataSchema('type_chart', $data, $identifier);
        }
    }

    /**
     * @return \Generator
     */
    public function getData(): \Generator
    {
        $finder = $this->getFinderForDirectory('type_chart');
        $finder->name('*.yaml');

        foreach ($finder as $fileInfo) {
            yield $fileInfo->getFilename() => $fileInfo->getContents();
        }
    }

    /**
     * Test this is a complete type chart and all matchups are listed.
     *
     * e.g. if a type is listed as attacking, it must also be defending.
     *
     * @depends testData
     */
    public function testMatchups(): void
    {
        $allData = $this->getData();
        foreach ($allData as $identifier => $yaml) {
            $data = $this->parseYaml($yaml);
            $matchups = $data['efficacy'];
            $attackingTypes = array_keys($matchups);
            sort($attackingTypes);
            foreach ($matchups as $attackingType => $efficacies) {
                $defendingTypes = array_keys($efficacies);
                sort($defendingTypes);
                self::assertEquals(
                    $attackingTypes,
                    $defendingTypes,
                    sprintf('[%s] Type matchups are incomplete', $identifier)
                );
            }
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
                'typeIdentifier' => new CsvIdentifierExists('type'),
            ],
        ];
    }
}
