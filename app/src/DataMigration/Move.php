<?php

namespace App\DataMigration;

use App\Entity\Embeddable\Range;
use App\Entity\MoveInVersionGroup;
use App\Entity\MoveStatChange;
use DragoonBoots\A2B\Annotations\DataMigration;
use DragoonBoots\A2B\Annotations\IdField;
use DragoonBoots\A2B\DataMigration\DataMigrationInterface;

/**
 * Move migration.
 *
 * @DataMigration(
 *     name="Move",
 *     source="/%kernel.project_dir%/resources/data/move",
 *     sourceDriver="DragoonBoots\A2B\Drivers\Source\YamlSourceDriver",
 *     sourceIds={@IdField(name="identifier", type="string")},
 *     destination="\App\Entity\Move",
 *     destinationDriver="DragoonBoots\A2B\Drivers\Destination\DoctrineDestinationDriver",
 *     destinationIds={@IdField(name="id")},
 *     depends={
 *       "App\DataMigration\MoveCategory",
 *       "App\DataMigration\MoveFlag",
 *       "App\DataMigration\MoveAilment",
 *       "App\DataMigration\MoveTarget",
 *       "App\DataMigration\VersionGroup",
 *       "App\DataMigration\MoveDamageClass",
 *       "App\DataMigration\MoveEffect",
 *       "App\DataMigration\ContestType",
 *       "App\DataMigration\ContestEffect",
 *       "App\DataMigration\SuperContestEffect",
 *       "App\DataMigration\Stat",
 *       "App\DataMigration\Type"
 *     }
 * )
 */
class Move extends AbstractDoctrineDataMigration implements DataMigrationInterface
{

    /**
     * {@inheritdoc}
     */
    public function transform($sourceData, $destinationData)
    {
        unset($sourceData['identifier']);
        foreach ($sourceData as $versionGroup => $versionGroupSource) {
            /** @var \App\Entity\VersionGroup $versionGroup */
            $versionGroup = $this->referenceStore->get(VersionGroup::class, ['identifier' => $versionGroup]);
            $versionGroupSource['version_group'] = $versionGroup;
            $versionGroupDestination = $destinationData->findChildByGrouping($versionGroup) ?? (new MoveInVersionGroup());
            $versionGroupDestination = $this->transformVersionGroup($versionGroupSource, $versionGroupDestination);
            $destinationData->addChild($versionGroupDestination);
        }

        return $destinationData;
    }

    /**
     * @param array              $sourceData
     * @param MoveInVersionGroup $destinationData
     *
     * @return MoveInVersionGroup
     */
    protected function transformVersionGroup(array $sourceData, MoveInVersionGroup $destinationData): MoveInVersionGroup
    {
        foreach ($sourceData['categories'] as &$category) {
            $category = $this->referenceStore->get(MoveCategory::class, ['identifier' => $category]);
        }
        if (isset($sourceData['flags'])) {
            foreach ($sourceData['flags'] as &$flag) {
                $flag = $this->referenceStore->get(MoveFlag::class, ['identifier' => $flag]);
            }
        }
        if (isset($sourceData['ailment'])) {
            $sourceData['ailment'] = $this->referenceStore->get(MoveAilment::class, ['identifier' => $sourceData['ailment']]);
        }
        $sourceData['hits'] = Range::fromString($sourceData['hits']);
        $sourceData['turns'] = Range::fromString($sourceData['turns']);
        $sourceData['type'] = $this->referenceStore->get(Type::class, ['identifier' => $sourceData['type']]);
        $sourceData['target'] = $this->referenceStore->get(MoveTarget::class, ['identifier' => $sourceData['target']]);
        $sourceData['damage_class'] = $this->referenceStore->get(MoveDamageClass::class, ['identifier' => $sourceData['damage_class']]);
        /** @var \App\Entity\MoveEffect $moveEffect */
        $moveEffect = $this->referenceStore->get(MoveEffect::class, ['id' => $sourceData['effect']]);
        $sourceData['effect'] = $moveEffect->findChildByGrouping($sourceData['version_group']);
        if (isset($sourceData['contest_type'])) {
            $sourceData['contest_type'] = $this->referenceStore->get(ContestType::class, ['identifier' => $sourceData['contest_type']]);
        }
        if (isset($sourceData['contest_effect'])) {
            $sourceData['contest_effect'] = $this->referenceStore->get(ContestEffect::class, ['id' => $sourceData['contest_effect']]);
        }
        unset($sourceData['contest_use_before']);
        if (isset($sourceData['super_contest_effect'])) {
            $sourceData['super_contest_effect'] = $this->referenceStore->get(SuperContestEffect::class, ['id' => $sourceData['super_contest_effect']]);
        }
        unset($sourceData['super_contest_use_before']);
        if (isset($sourceData['stat_changes'])) {
            foreach ($sourceData['stat_changes'] as $statIdentifier => &$change) {
                $statChange = null;
                foreach ($destinationData->getStatChanges() as $testStatChange) {
                    if ($testStatChange->getStat()->getSlug()) {
                        $statChange = $testStatChange;
                        break;
                    }
                }
                if (!isset($statChange)) {
                    $statChange = new MoveStatChange();
                    $statChange->setStat($this->referenceStore->get(Stat::class, ['identifier' => $statIdentifier]));
                }
                $statChange->setChange($change);
                $change = $statChange;
            }
            $sourceData['stat_changes'] = array_values($sourceData['stat_changes']);
        }

        /** @var MoveInVersionGroup $destinationData */
        $destinationData = $this->mergeProperties($sourceData, $destinationData);

        return $destinationData;
    }

    /**
     * {@inheritdoc}
     */
    public function defaultResult()
    {
        return new \App\Entity\Move();
    }
}
