<?php

namespace App\DataMigration;

use DragoonBoots\A2B\Annotations\DataMigration;
use DragoonBoots\A2B\Annotations\IdField;
use DragoonBoots\A2B\DataMigration\DataMigrationInterface;

/**
 * Move Learn Method migration.
 *
 * @DataMigration(
 *     name="Move Learn Method",
 *     source="yaml:///%kernel.project_dir%/resources/data/move_learn_method",
 *     sourceIds={@IdField(name="identifier", type="string")},
 *     destination="doctrine:///App/Entity/MoveLearnMethod",
 *     destinationIds={@IdField(name="id")}
 * )
 */
class MoveLearnMethod extends AbstractDoctrineDataMigration implements DataMigrationInterface
{

    /**
     * {@inheritdoc}
     */
    public function transform($sourceData, $destinationData)
    {
        $sourceData['slug'] = $sourceData['identifier'];
        unset($sourceData['identifier']);
        $sourceData['position'] = $sourceData['sort'];

        $properties = [
            'name',
            'slug',
            'position',
            'description',
        ];
        $destinationData = $this->mergeProperties($sourceData, $destinationData, $properties);

        return $destinationData;
    }

    /**
     * {@inheritdoc}
     */
    public function defaultResult()
    {
        return new \App\Entity\MoveLearnMethod();
    }
}
