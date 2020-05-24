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
 *     source="/%kernel.project_dir%/resources/data/move_effect",
 *     sourceDriver="DragoonBoots\A2B\Drivers\Source\YamlSourceDriver",
 *     sourceIds={@IdField(name="id")},
 *     destination="\App\Entity\MoveEffect",
 *     destinationDriver="DragoonBoots\A2B\Drivers\Destination\DoctrineDestinationDriver",
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
        $destinationData->setId($sourceData['id']);
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
        /** @var MoveEffectInVersionGroup $destinationData */
        $destinationData = $this->mergeProperties($sourceData, $destinationData);

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
