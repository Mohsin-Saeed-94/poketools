<?php

namespace App\Tests\dataschema;


use App\Tests\dataschema\Filter\CsvIdentifierExists;
use App\Tests\Traits\YamlParserTrait;

/**
 * Test Nature
 *
 * @group data
 * @group nature
 * @coversNothing
 */
class NatureTest extends DataSchemaTestCase
{

    use YamlParserTrait;

    /**
     * Test data matches schema
     *
     * @dataProvider dataProvider
     */
    public function testData(array $data): void
    {
        $this->assertDataSchema('nature', $data);
    }

    public function dataProvider()
    {
        return $this->buildYamlDataProvider('nature');
    }

    /**
     * @inheritDoc
     */
    protected function getFilters(): array
    {
        return [
            'string' => [
                'statIdentifier' => new CsvIdentifierExists('stat'),
                'berryFlavorIdentifier' => new CsvIdentifierExists('berry_flavor'),
                'battleStyleIdentifier' => new CsvIdentifierExists('battle_style'),
                'pokeathlonStatIdentifier' => new CsvIdentifierExists('pokeathlon_stat'),
            ],
        ];
    }

}
