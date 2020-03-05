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
 *     source="/%kernel.project_dir%/resources/data/version_group",
 *     sourceDriver="DragoonBoots\A2B\Drivers\Source\YamlSourceDriver",
 *     sourceIds={@IdField(name="identifier", type="string")},
 *     destination="\App\Entity\VersionGroup",
 *     destinationDriver="DragoonBoots\A2B\Drivers\Destination\DoctrineDestinationDriver",
 *     destinationIds={@IdField(name="id")},
 *     depends={
 *         "App\DataMigration\Feature",
 *         "App\DataMigration\Generation"
 *     }
 * )
 */
class VersionGroup extends AbstractDoctrineDataMigration implements DataMigrationInterface
{

    /**
     * {@inheritdoc}
     * @param \App\Entity\VersionGroup $destinationData
     */
    public function transform($sourceData, $destinationData)
    {
        $sourceData['generation'] = $this->referenceStore->get(Generation::class, ['id' => $sourceData['generation']]);
        $properties = [
            'name',
            'position',
            'generation',
        ];
        $destinationData = $this->mergeProperties($sourceData, $destinationData, $properties);
        foreach ($sourceData['features'] as $featureIdentifier) {
            $destinationData->addFeature(
                $this->referenceStore->get(Feature::class, ['identifier' => $featureIdentifier])
            );
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
