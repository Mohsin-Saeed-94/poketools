<?php

namespace App\DataMigration;

use DragoonBoots\A2B\Annotations\DataMigration;
use DragoonBoots\A2B\Annotations\IdField;
use DragoonBoots\A2B\DataMigration\DataMigrationInterface;

/**
 * Version migration.
 *
 * @DataMigration(
 *     name="Version",
 *     source="csv:///%kernel.project_dir%/resources/data/version.csv",
 *     sourceIds={@IdField(name="identifier", type="string")},
 *     destination="doctrine:///App/Entity/Version",
 *     destinationIds={@IdField(name="id")},
 *     depends={"App\DataMigration\VersionGroup"}
 * )
 */
class Version extends AbstractDoctrineDataMigration implements DataMigrationInterface
{

    /**
     * {@inheritdoc}
     * @param \App\Entity\Version $destinationData
     */
    public function transform($sourceData, $destinationData)
    {
        unset($sourceData['identifier']);
        $sourceData['version_group'] = $this->referenceStore->get(VersionGroup::class, ['identifier' => $sourceData['version_group']]);
        static $position = 1;
        $sourceData['position'] = $position;
        $position++;

        $properties = array_keys($sourceData);
        $destinationData = $this->mergeProperties($properties, $sourceData, $destinationData);

        return $destinationData;
    }

    /**
     * @inheritDoc
     */
    public function defaultResult()
    {
        return new \App\Entity\Version();
    }
}
