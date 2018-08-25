<?php

namespace App\DataMigration;

use DragoonBoots\A2B\Annotations\DataMigration;
use DragoonBoots\A2B\Annotations\IdField;
use DragoonBoots\A2B\DataMigration\DataMigrationInterface;

/**
 * Generation migration.
 *
 * @DataMigration(
 *     name="Generation",
 *     source="csv:///%kernel.project_dir%/resources/data/generation.csv",
 *     sourceIds={@IdField(name="id")},
 *     destination="doctrine:///App/Entity/Generation",
 *     destinationIds={@IdField(name="id")}
 * )
 */
class Generation extends AbstractDoctrineDataMigration implements DataMigrationInterface
{

    /**
     * {@inheritdoc}
     */
    public function transform($sourceData, $destinationData)
    {
        $sourceData['number'] = $sourceData['id'];
        $sourceData['position'] = $sourceData['id'];
        $properties = [
            'name',
            'number',
            'position',
        ];
        $this->mergeProperties($properties, $sourceData, $destinationData);

        return $destinationData;
    }

    /**
     * {@inheritdoc}
     */
    public function defaultResult()
    {
        return new \App\Entity\Generation();
    }
}
