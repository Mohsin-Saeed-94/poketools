<?php

namespace App\Tests\dataschema;


use App\Tests\dataschema\Filter\CssColor;
use App\Tests\Traits\CsvParserTrait;

/**
 * Test Pokemon Color
 *
 * @group data
 * @group pokemon_color
 * @coversNothing
 */
class PokemonColorTest extends DataSchemaTestCase
{

    use CsvParserTrait;

    /**
     * Test data matches schema
     *
     * @dataProvider dataProvider
     */
    public function testData(array $row): void
    {
        $this->assertDataSchema('pokemon_color', $row);
    }

    public function dataProvider()
    {
        return $this->buildCsvDataProvider('pokemon_color', 'identifier');
    }

    protected function getFilters(): array
    {
        return [
            'string' => [
                'cssColor' => new CssColor(),
            ],
        ];
    }

}
