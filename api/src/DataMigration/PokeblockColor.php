<?php

namespace App\DataMigration;

use DragoonBoots\A2B\Annotations\DataMigration;
use DragoonBoots\A2B\Annotations\IdField;
use DragoonBoots\A2B\DataMigration\DataMigrationInterface;

/**
 * Pokéblock Color migration.
 *
 * @DataMigration(
 *     name="Pokéblock Color",
 *     source="csv:///%kernel.project_dir%/resources/data/pokeblock_color.csv",
 *     sourceIds={@IdField(name="identifier", type="string")},
 *     destination="doctrine:///App/Entity/PokeblockColor",
 *     destinationIds={@IdField(name="id")}
 * )
 */
class PokeblockColor extends AbstractDoctrineDataMigration implements DataMigrationInterface
{

    /**
     * {@inheritdoc}
     */
    public function transform($sourceData, $destinationData)
    {
        unset($sourceData['identifier']);
        $destinationData = $this->mergeProperties($sourceData, $destinationData);

        return $destinationData;
    }

    /**
     * {@inheritdoc}
     */
    public function defaultResult()
    {
        return new \App\Entity\PokeblockColor();
    }
}
