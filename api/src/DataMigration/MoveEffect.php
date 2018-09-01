<?php

namespace App\DataMigration;

use App\Entity\MoveEffectInVersionGroup;
use DragoonBoots\A2B\Annotations\DataMigration;
use DragoonBoots\A2B\Annotations\IdField;
use DragoonBoots\A2B\DataMigration\DataMigrationInterface;

/**
 * Move Effect migration.
 *
 * @DataMigration(
 *     name="Move Effect",
 *     source="yaml:///%kernel.project_dir%/resources/data/move_effect",
 *     sourceIds={@IdField(name="id")},
 *     destination="doctrine:///App/Entity/MoveEffect",
 *     destinationIds={@IdField(name="id")},
 *     depends={"App\DataMigration\VersionGroup"}
 * )
 */
class MoveEffect extends AbstractDoctrineDataMigration implements DataMigrationInterface
{

    /**
     * {@inheritdoc}
     * @param \App\Entity\MoveEffect $destinationData
     */
    public function transform($sourceData, $destinationData)
    {
        unset($sourceData['id']);
        foreach ($sourceData as $versionGroup => $versionGroupSource) {
            /** @var \App\Entity\VersionGroup $versionGroup */
            $versionGroup = $this->referenceStore->get(VersionGroup::class, ['identifier' => $versionGroup]);
            $versionGroupSource['version_group'] = $versionGroup;
            $versionGroupDestination = $destinationData->findChildByGrouping($versionGroup) ?? (new MoveEffectInVersionGroup());
            $versionGroupDestination = $this->transformVersionGroup($versionGroupSource, $versionGroupDestination);
            $destinationData->addChild($versionGroupDestination);
        }

        return $destinationData;
    }

    /**
     * @param array                                $sourceData
     * @param \App\Entity\MoveEffectInVersionGroup $destinationData
     *
     * @return MoveEffectInVersionGroup
     */
    protected function transformVersionGroup($sourceData, $destinationData)
    {
        $properties = array_keys($sourceData);
        /** @var MoveEffectInVersionGroup $destinationData */
        $destinationData = $this->mergeProperties($properties, $sourceData, $destinationData);

        return $destinationData;
    }

    /**
     * {@inheritdoc}
     */
    public function defaultResult()
    {
        return new \App\Entity\MoveEffect();
    }
}
