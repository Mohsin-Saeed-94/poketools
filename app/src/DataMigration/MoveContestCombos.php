<?php

namespace App\DataMigration;

use DragoonBoots\A2B\Annotations\DataMigration;
use DragoonBoots\A2B\Annotations\IdField;
use DragoonBoots\A2B\DataMigration\AbstractDataMigration;
use DragoonBoots\A2B\DataMigration\DataMigrationInterface;
use DragoonBoots\A2B\Drivers\DestinationDriverInterface;
use DragoonBoots\A2B\Drivers\SourceDriverInterface;

/**
 * Move Contest Combos migration.
 *
 * @DataMigration(
 *     name="Move Contest Combos",
 *     source="/%kernel.project_dir%/resources/data/move",
 *     sourceDriver="DragoonBoots\A2B\Drivers\Source\YamlSourceDriver",
 *     sourceIds={@IdField(name="identifier", type="string")},
 *     destination="\App\Entity\Move",
 *     destinationDriver="DragoonBoots\A2B\Drivers\Destination\DoctrineDestinationDriver",
 *     destinationIds={@IdField(name="id")},
 *     depends={"App\DataMigration\Move", "App\DataMigration\VersionGroup"},
 *     extends="App\DataMigration\Move"
 * )
 */
class MoveContestCombos extends AbstractDoctrineDataMigration implements DataMigrationInterface
{

    /**
     * {@inheritdoc}
     * @param \App\Entity\Move $destinationData
     */
    public function transform($sourceData, $destinationData)
    {
        foreach ($sourceData as $versionGroup => $versionGroupSourceData) {
            if (isset($versionGroupSourceData['contest_use_before'])) {
                /** @var \App\Entity\VersionGroup $versionGroup */
                $versionGroup = $this->referenceStore->get(VersionGroup::class, ['identifier' => $versionGroup]);
                $versionGroupDestinationData = $destinationData->findChildByGrouping($versionGroup);
                foreach ($versionGroupSourceData['contest_use_before'] as &$contestUseBefore) {
                    /** @var \App\Entity\Move $contestUseBefore */
                    $contestUseBefore = $this->referenceStore->get(Move::class, ['identifier' => $contestUseBefore], true);
                    $contestUseBefore = $contestUseBefore->findChildByGrouping($versionGroup);
                }
                $this->mergeProperties($versionGroupSourceData, $versionGroupDestinationData, ['contest_use_before']);
            }
            if (isset($versionGroupSourceData['super_contest_use_before'])) {
                /** @var \App\Entity\VersionGroup $versionGroup */
                $versionGroup = $this->referenceStore->get(VersionGroup::class, ['identifier' => $versionGroup]);
                $versionGroupDestinationData = $destinationData->findChildByGrouping($versionGroup);
                foreach ($versionGroupSourceData['super_contest_use_before'] as &$superContestUseBefore) {
                    /** @var \App\Entity\Move $contestUseBefore */
                    $superContestUseBefore = $this->referenceStore->get(Move::class, ['identifier' => $superContestUseBefore], true);
                    $superContestUseBefore = $superContestUseBefore->findChildByGrouping($versionGroup);
                }
                $this->mergeProperties($versionGroupSourceData, $versionGroupDestinationData, ['super_contest_use_before']);
            }
        }

        return $destinationData;
    }

    /**
     * @inheritDoc
     */
    public function defaultResult()
    {
        throw new \LogicException('The destination must already exist for contest combos');
    }
}
