<?php

namespace App\DataMigration;

use DragoonBoots\A2B\Annotations\DataMigration;
use DragoonBoots\A2B\Annotations\IdField;
use DragoonBoots\A2B\DataMigration\DataMigrationInterface;

/**
 * Contest Type migration.
 *
 * @DataMigration(
 *     name="Contest Type",
 *     source="/%kernel.project_dir%/resources/data/contest_type.csv",
 *     sourceDriver="DragoonBoots\A2B\Drivers\Source\CsvSourceDriver",
 *     sourceIds={@IdField(name="identifier", type="string")},
 *     destination="\App\Entity\ContestType",
 *     destinationDriver="DragoonBoots\A2B\Drivers\Destination\DoctrineDestinationDriver",
 *     destinationIds={@IdField(name="id")},
 *     depends={"App\DataMigration\BerryFlavor", "App\DataMigration\PokeblockColor"}
 * )
 */
class ContestType extends AbstractDoctrineDataMigration implements DataMigrationInterface
{

    /**
     * {@inheritdoc}
     */
    public function transform($sourceData, $destinationData)
    {
        unset($sourceData['identifier']);

        $sourceData['berry_flavor'] = $this->referenceStore->get(BerryFlavor::class, ['identifier' => $sourceData['berry_flavor']]);
        $sourceData['pokeblock_color'] = $this->referenceStore->get(PokeblockColor::class, ['identifier' => $sourceData['pokeblock_color']]);
        static $position = 1;
        $sourceData['position'] = $position;
        $position++;

        $destinationData = $this->mergeProperties($sourceData, $destinationData);

        return $destinationData;
    }

    /**
     * @inheritDoc
     */
    public function defaultResult()
    {
        return new \App\Entity\ContestType();
    }
}
