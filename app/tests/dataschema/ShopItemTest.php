<?php

namespace App\Tests\dataschema;


use App\Tests\data\CsvParserTrait;
use App\Tests\data\YamlParserTrait;
use App\Tests\dataschema\Filter\EntityHasVersionGroup;
use App\Tests\dataschema\Filter\YamlIdentifierExists;

/**
 * Test Shop Item
 *
 * @group data
 * @group shop_item
 * @coversNothing
 */
class ShopItemTest extends DataSchemaTestCase
{
    use CsvParserTrait;
    use YamlParserTrait;

    /**
     * Test data matches schema
     */
    public function testData(): void
    {
        $allData = $this->getIteratorForCsv('shop_item');
        $this->assertDataSchema('shop_item', $allData);
    }

    /**
     * Test that the location, area, and shop exist
     *
     * @depends testData
     */
    public function testLocationAreaShop(): void
    {
        $allData = $this->getIteratorForCsv('shop_item');
        $locations = array_column($allData, 'location');

        // Check locations exist
        foreach (array_unique($locations) as $location) {
            $locationFilePath = sprintf('%s/%s.yaml', realpath(self::BASE_DIR_DATA.'/location'), $location);
            self::assertFileExists(
                $locationFilePath,
                sprintf('The location "%s" does not exist.', $location)
            );
        }

        // Check location and areas are proper in the version
        foreach ($allData as $shopItem) {
            $location = $shopItem['location'];
            $locationFilePath = sprintf('%s/%s.yaml', realpath(self::BASE_DIR_DATA.'/location'), $location);
            $locationData = $this->getDataFromYaml($locationFilePath);

            self::assertArrayHasKey(
                $shopItem['version_group'],
                $locationData,
                sprintf(
                    'The location "%s" does not exist in the version group "%s".',
                    $location,
                    $shopItem['version_group']
                )
            );

            $area = $this->assertAreaInLocation(
                explode('/', $shopItem['area']),
                $locationData[$shopItem['version_group']]['areas'],
                $shopItem['location'],
                $shopItem['area'],
                $shopItem['version_group']
            );

            // Check shop
            self::assertArrayHasKey(
                'shops',
                $area,
                sprintf('The location "%s" area "%s" has no shops defined.', $shopItem['location'], $shopItem['area'])
            );
            self::assertArrayHasKey(
                $shopItem['shop'],
                $area['shops'],
                sprintf(
                    'The location "%s" area "%s" has no shop called "%s".',
                    $shopItem['location'],
                    $shopItem['area'],
                    $shopItem['shop']
                )
            );
        }
    }

    private function assertAreaInLocation(
        array $areaIdentifierParts,
        array $areas,
        string $location,
        string $areaIdentifier,
        string $versionGroup
    ): array {
        $searchIdentifier = array_shift($areaIdentifierParts);
        self::assertArrayHasKey(
            $searchIdentifier,
            $areas,
            sprintf(
                'The location "%s" does not have the area "%s" in the version group "%s".',
                $location,
                $areaIdentifier,
                $versionGroup
            )
        );

        if ($areaIdentifierParts) {
            self::assertArrayHasKey(
                'children',
                $areas[$searchIdentifier],
                sprintf(
                    'The location "%s" area path "%s" does not exist because parent(s) are missing.',
                    $location,
                    $areaIdentifier
                )
            );

            return $this->assertAreaInLocation(
                $areaIdentifierParts,
                $areas[$searchIdentifier]['children'],
                $location,
                $areaIdentifier,
                $versionGroup
            );
        }

        return $areas[$searchIdentifier];
    }

    /**
     * @inheritDoc
     */
    protected function getFilters(): array
    {
        return [
            'string' => [
                'versionGroupIdentifier' => new YamlIdentifierExists('version_group'),
                'locationIdentifier' => new YamlIdentifierExists('location'),
                'locationInVersionGroup' => new EntityHasVersionGroup('location'),
                'itemIdentifier' => new YamlIdentifierExists('item'),
                'itemInVersionGroup' => new EntityHasVersionGroup('item'),
            ],
        ];
    }

}
