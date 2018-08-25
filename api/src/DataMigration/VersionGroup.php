<?php

namespace App\DataMigration;

use DragoonBoots\A2B\Annotations\DataMigration;
use DragoonBoots\A2B\Annotations\IdField;
use DragoonBoots\A2B\DataMigration\DataMigrationInterface;

/**
 * Version Group migration.
 *
 * @DataMigration(
 *     name="Version Group",
 *     source="yaml:///%kernel.project_dir%/resources/data/version_group",
 *     sourceIds={@IdField(name="identifier", type="string")},
 *     destination="doctrine:///App/Entity/VersionGroup",
 *     destinationIds={@IdField(name="id")},
 *     depends={
 *         "App\DataMigration\Generation",
 *         "App\DataMigration\Region"
 *     }
 * )
 */
class VersionGroup extends AbstractDoctrineDataMigration implements DataMigrationInterface
{

    /**
     * {@inheritdoc}
     * @param \App\Entity\VersionGroup $destinationData
     *
     * @todo Migrate features
     */
    public function transform($sourceData, $destinationData)
    {
        $sourceData['position'] = $sourceData['order'];
        $sourceData['generation'] = $this->referenceStore->get(Generation::class, ['id' => $sourceData['generation']]);
        $properties = [
            'name',
            'position',
            'generation',
        ];
        $destinationData = $this->mergeProperties($properties, $sourceData, $destinationData);
        foreach ($sourceData['regions'] as $regionIdentifier) {
            $destinationData->addRegion($this->referenceStore->get(Region::class, ['identifier' => $regionIdentifier]));
        }

        return $destinationData;
    }

    /**
     * {@inheritdoc}
     */
    public function defaultResult()
    {
        return new \App\Entity\VersionGroup();
    }
}
