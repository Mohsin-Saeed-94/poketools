<?php

namespace App\DataMigration;

use App\Entity\Type;
use App\Entity\TypeEfficacy;
use DragoonBoots\A2B\Annotations\DataMigration;
use DragoonBoots\A2B\Annotations\IdField;
use DragoonBoots\A2B\DataMigration\DataMigrationInterface;

/**
 * Type Chart migration.
 *
 * @DataMigration(
 *     name="Type Chart",
 *     source="/%kernel.project_dir%/resources/data/type_chart",
 *     sourceDriver="DragoonBoots\A2B\Drivers\Source\YamlSourceDriver",
 *     sourceIds={@IdField(name="id")},
 *     destination="\App\Entity\TypeChart",
 *     destinationDriver="DragoonBoots\A2B\Drivers\Destination\DoctrineDestinationDriver",
 *     destinationIds={@IdField(name="id")},
 *     depends={"App\DataMigration\VersionGroup", "App\DataMigration\Type"}
 * )
 */
class TypeChart extends AbstractDoctrineDataMigration implements DataMigrationInterface
{

    /**
     * {@inheritdoc}
     * @param \App\Entity\TypeChart $destinationData
     */
    public function transform($sourceData, $destinationData)
    {
        unset($sourceData['id']);

        foreach ($sourceData['version_groups'] as $versionGroup) {
            $versionGroup = $this->referenceStore->get(VersionGroup::class, ['identifier' => $versionGroup]);
            $destinationData->addVersionGroup($versionGroup);
        }

        foreach ($sourceData['efficacy'] as $attackingType => $attackingTypeEfficacy) {
            /** @var Type $attackingType */
            $attackingType = $this->referenceStore->get(\App\DataMigration\Type::class, ['identifier' => $attackingType]);
            foreach ($attackingTypeEfficacy as $defendingType => $efficacy) {
                /** @var Type $defendingType */
                $defendingType = $this->referenceStore->get(\App\DataMigration\Type::class, ['identifier' => $defendingType]);
                $this->addEfficacyForTypes($destinationData, $attackingType, $defendingType, $efficacy);
            }
        }
        $destinationData->sortEfficacies();

        return $destinationData;
    }

    /**
     * @param \App\Entity\TypeChart $destinationData
     * @param Type                  $attackingType
     * @param Type                  $defendingType
     * @param int                   $efficacy
     */
    protected function addEfficacyForTypes(\App\Entity\TypeChart $destinationData, Type $attackingType, Type $defendingType, int $efficacy)
    {
        // Update the existing efficacy, if already stored.
        foreach ($destinationData->getEfficacies() as $existingTypeEfficacy) {
            if ($existingTypeEfficacy->getAttackingType() === $attackingType && $existingTypeEfficacy->getDefendingType() === $defendingType) {
                $typeEfficacy = $existingTypeEfficacy;
                break;
            }
        }
        if (!isset($typeEfficacy)) {
            $typeEfficacy = new TypeEfficacy();
            $typeEfficacy->setAttackingType($attackingType)
                ->setDefendingType($defendingType);
        }
        $typeEfficacy->setEfficacy($efficacy);

        $destinationData->addEfficacy($typeEfficacy);
    }

    /**
     * {@inheritdoc}
     */
    public function defaultResult()
    {
        return new \App\Entity\TypeChart();
    }
}
