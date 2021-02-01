<?php

namespace App\Tests\dataschema;


use App\Tests\dataschema\Filter\CsvIdentifierExists;
use App\Tests\dataschema\Filter\YamlIdentifierExists;
use App\Tests\Traits\YamlParserTrait;

/**
 * Test Type Chart
 *
 * @group data
 * @group type_chart
 * @coversNothing
 */
class TypeChartTest extends DataSchemaTestCase
{

    use YamlParserTrait;

    /**
     * Test data matches schema
     *
     * @dataProvider dataProvider
     */
    public function testData(array $data): void
    {
        $this->assertDataSchema('type_chart', $data);
    }

    public function dataProvider()
    {
        return $this->buildYamlDataProvider('type_chart');
    }

    /**
     * Test this is a complete type chart and all matchups are listed.
     *
     * e.g. if a type is listed as attacking, it must also be defending.
     *
     * @depends      testData
     *
     * @dataProvider dataProvider
     */
    public function testMatchups(array $data): void
    {
        $attackingTypes = array_keys($data['efficacy']);
        sort($attackingTypes);
        foreach ($data['efficacy'] as $attackingType => $efficacies) {
            $defendingTypes = array_keys($efficacies);
            sort($defendingTypes);
            self::assertEquals(
                $attackingTypes,
                $defendingTypes,
                'Type matchups are incomplete'
            );
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
