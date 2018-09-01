<?php

namespace App\DataMigration;

use App\Entity\AbilityInVersionGroup;
use DragoonBoots\A2B\Annotations\DataMigration;
use DragoonBoots\A2B\Annotations\IdField;
use DragoonBoots\A2B\DataMigration\DataMigrationInterface;

/**
 * Ability migration.
 *
 * @DataMigration(
 *     name="Ability",
 *     source="yaml:///%kernel.project_dir%/resources/data/ability",
 *     sourceIds={@IdField(name="identifier", type="string")},
 *     destination="doctrine:///App/Entity/Ability",
 *     destinationIds={@IdField(name="id")},
 *     depends={"App\DataMigration\VersionGroup"}
 * )
 */
class Ability extends AbstractDoctrineDataMigration implements DataMigrationInterface
{

    /**
     * {@inheritdoc}
     * @param \App\Entity\Ability $destinationData
     */
    public function transform($sourceData, $destinationData)
    {
        unset($sourceData['identifier']);
        foreach ($sourceData as $versionGroup => $versionGroupSource) {
            /** @var \App\Entity\VersionGroup $versionGroup */
            $versionGroup = $this->referenceStore->get(VersionGroup::class, ['identifier' => $versionGroup]);
            $versionGroupSource['version_group'] = $versionGroup;
            $versionGroupDestination = $destinationData->findChildByGrouping($versionGroup) ?? (new AbilityInVersionGroup());
            $versionGroupDestination = $this->transformVersionGroup($versionGroupSource, $versionGroupDestination);
            $destinationData->addChild($versionGroupDestination);
        }

        return $destinationData;
    }

    /**
     * @param array                             $sourceData
     * @param \App\Entity\AbilityInVersionGroup $destinationData
     *
     * @return AbilityInVersionGroup
     */
    protected function transformVersionGroup($sourceData, $destinationData)
    {
        $properties = array_keys($sourceData);
        /** @var AbilityInVersionGroup $destinationData */
        $destinationData = $this->mergeProperties($properties, $sourceData, $destinationData);

        return $destinationData;
    }

    /**
     * @inheritDoc
     */
    public function defaultResult()
    {
        return new \App\Entity\Ability();
    }
}
