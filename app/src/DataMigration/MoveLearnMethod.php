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
 *     source="/%kernel.project_dir%/resources/data/move_learn_method",
 *     sourceDriver="DragoonBoots\A2B\Drivers\Source\YamlSourceDriver",
 *     sourceIds={@IdField(name="identifier", type="string")},
 *     destination="\App\Entity\MoveLearnMethod",
 *     destinationDriver="DragoonBoots\A2B\Drivers\Destination\DoctrineDestinationDriver",
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
