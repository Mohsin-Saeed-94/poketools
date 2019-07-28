<?php

namespace App\Tests\dataschema;


use App\Tests\data\DataFinderTrait;
use App\Tests\data\YamlParserTrait;
use App\Tests\dataschema\Filter\CsvIdentifierExists;

/**
 * Test Nature
 *
 * @group data
 * @group nature
 * @coversNothing
 */
class NatureTest extends DataSchemaTestCase
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
            $this->assertDataSchema('nature', $data, $identifier);
        }
    }

    /**
     * @return \Generator
     */
    public function getData(): \Generator
    {
        $finder = $this->getFinderForDirectory('nature');
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
                'statIdentifier' => new CsvIdentifierExists('stat'),
                'berryFlavorIdentifier' => new CsvIdentifierExists('berry_flavor'),
                'battleStyleIdentifier' => new CsvIdentifierExists('battle_style'),
                'pokeathlonStatIdentifier' => new CsvIdentifierExists('pokeathlon_stat'),
            ],
        ];
    }
}
