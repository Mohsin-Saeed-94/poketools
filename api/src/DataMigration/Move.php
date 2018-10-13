<?php

namespace App\DataMigration;

use App\Entity\Embeddable\Range;
use App\Entity\MoveInVersionGroup;
use DragoonBoots\A2B\Annotations\DataMigration;
use DragoonBoots\A2B\Annotations\IdField;
use DragoonBoots\A2B\DataMigration\DataMigrationInterface;

/**
 * Move migration.
 *
 * @DataMigration(
 *     name="Move",
 *     source="yaml:///%kernel.project_dir%/resources/data/move",
 *     sourceIds={@IdField(name="identifier", type="string")},
 *     destination="doctrine:///App/Entity/Move",
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
        if (isset($sourceData['contest_use_before'])) {
            foreach ($sourceData['contest_use_before'] as &$contestUseBefore) {
                /** @var \App\Entity\Move $contestUseBefore */
                $contestUseBefore = $this->referenceStore->get(Move::class, ['identifier' => $contestUseBefore], true);
                $contestUseBefore = $contestUseBefore->findChildByGrouping($sourceData['version_group']);
            }
        }
        if (isset($sourceData['super_contest_effect'])) {
            $sourceData['super_contest_effect'] = $this->referenceStore->get(SuperContestEffect::class, ['id' => $sourceData['super_contest_effect']]);
        }
        if (isset($sourceData['super_contest_use_before'])) {
            foreach ($sourceData['super_contest_use_before'] as &$superContestUseBefore) {
                /** @var \App\Entity\Move $superContestUseBefore */
                $superContestUseBefore = $this->referenceStore->get(Move::class, ['identifier' => $superContestUseBefore], true);
                $superContestUseBefore = $superContestUseBefore->findChildByGrouping($sourceData['version_group']);
            }
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
