<?php

namespace App\DataMigration;

use App\Entity\ContestEffectInVersionGroup;
use DragoonBoots\A2B\Annotations\DataMigration;
use DragoonBoots\A2B\Annotations\IdField;
use DragoonBoots\A2B\DataMigration\DataMigrationInterface;

/**
 * Contest Effect migration.
 *
 * @DataMigration(
 *     name="Contest Effect",
 *     source="/%kernel.project_dir%/resources/data/contest_effect",
 *     sourceDriver="DragoonBoots\A2B\Drivers\Source\YamlSourceDriver",
 *     sourceIds={@IdField(name="id")},
 *     destination="\App\Entity\ContestEffect",
 *     destinationDriver="DragoonBoots\A2B\Drivers\Destination\DoctrineDestinationDriver",
 *     destinationIds={@IdField(name="id")}
 * )
 */
class ContestEffect extends AbstractDoctrineDataMigration implements DataMigrationInterface
{

    /**
     * {@inheritdoc}
     */
    public function transform($sourceData, $destinationData)
    {
        $destinationData->setId($sourceData['id']);
        unset($sourceData['id']);
        foreach ($sourceData as $versionGroup => $versionGroupSource) {
            /** @var \App\Entity\VersionGroup $versionGroup */
            $versionGroup = $this->referenceStore->get(VersionGroup::class, ['identifier' => $versionGroup]);
            $versionGroupSource['version_group'] = $versionGroup;
            $versionGroupDestination = $destinationData->findChildByGrouping(
                    $versionGroup
                ) ?? (new ContestEffectInVersionGroup());
            $versionGroupDestination = $this->transformVersionGroup($versionGroupSource, $versionGroupDestination);
            $destinationData->addChild($versionGroupDestination);
        }

        return $destinationData;
    }

    /**
     * @param array $sourceData
     * @param \App\Entity\ContestEffectInVersionGroup $destinationData
     *
     * @return ContestEffectInVersionGroup
     */
    protected function transformVersionGroup($sourceData, $destinationData)
    {
        /** @var ContestEffectInVersionGroup $destinationData */
        $destinationData = $this->mergeProperties($sourceData, $destinationData);

        return $destinationData;
    }

    /**
     * {@inheritdoc}
     */
    public function defaultResult()
    {
        return new \App\Entity\ContestEffect();
    }
}
