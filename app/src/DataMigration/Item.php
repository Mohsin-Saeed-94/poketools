<?php

namespace App\DataMigration;

use App\Entity\Berry;
use App\Entity\BerryFlavorLevel;
use App\Entity\Embeddable\Range;
use App\Entity\ItemInVersionGroup;
use App\Entity\Machine;
use App\Entity\VersionGroup;
use DragoonBoots\A2B\Annotations\DataMigration;
use DragoonBoots\A2B\Annotations\IdField;
use DragoonBoots\A2B\DataMigration\DataMigrationInterface;
use PhpUnitsOfMeasure\PhysicalQuantity\Length;
use PhpUnitsOfMeasure\PhysicalQuantity\Time;

/**
 * Item migration.
 *
 * @DataMigration(
 *     name="Item",
 *     source="/%kernel.project_dir%/resources/data/item",
 *     sourceDriver="DragoonBoots\A2B\Drivers\Source\YamlSourceDriver",
 *     sourceIds={@IdField(name="identifier", type="string")},
 *     destination="\App\Entity\Item",
 *     destinationDriver="DragoonBoots\A2B\Drivers\Destination\DoctrineDestinationDriver",
 *     destinationIds={@IdField(name="id")},
 *     depends={
 *         "App\DataMigration\VersionGroup",
 *         "App\DataMigration\ItemCategory",
 *         "App\DataMigration\ItemFlingEffect",
 *         "App\DataMigration\ItemFlag",
 *         "App\DataMigration\ItemPocket",
 *         "App\DataMigration\BerryFirmness",
 *         "App\DataMigration\BerryFlavor",
 *         "App\DataMigration\Type",
 *         "App\DataMigration\Move"
 *     }
 * )
 */
class Item extends AbstractDoctrineDataMigration implements DataMigrationInterface
{

    /**
     * {@inheritdoc}
     * @param \App\Entity\Item $destinationData
     */
    public function transform($sourceData, $destinationData)
    {
        $identifier = $sourceData['identifier'];
        unset($sourceData['identifier']);
        foreach ($sourceData as $versionGroup => $versionGroupSourceData) {
            /** @var VersionGroup $versionGroup */
            $versionGroup = $this->referenceStore->get(\App\DataMigration\VersionGroup::class, ['identifier' => $versionGroup]);
            $versionGroupDestinationData = $destinationData->findChildByGrouping($versionGroup);
            if (!$versionGroupDestinationData) {
                $versionGroupDestinationData = new ItemInVersionGroup();
                $versionGroupDestinationData->setVersionGroup($versionGroup);
            }

            $versionGroupSourceData['slug'] = $identifier;
            $versionGroupSourceData['category'] = $this->referenceStore->get(ItemCategory::class, ['identifier' => $versionGroupSourceData['category']]);
            /** @var \App\Entity\ItemPocket $itemPocket */
            $itemPocket = $this->referenceStore->get(ItemPocket::class, ['identifier' => $versionGroupSourceData['pocket']]);
            $versionGroupSourceData['pocket'] = $itemPocket->findChildByGrouping($versionGroup);

            if (isset($versionGroupSourceData['fling_effect'])) {
                $versionGroupSourceData['fling_effect'] = $this->referenceStore->get(ItemFlingEffect::class, ['identifier' => $versionGroupSourceData['fling_effect']]);
            }

            if (isset($versionGroupSourceData['flags'])) {
                foreach ($versionGroupSourceData['flags'] as &$flag) {
                    $flag = $this->referenceStore->get(ItemFlag::class, ['identifier' => $flag]);
                }
            }

            if (isset($versionGroupSourceData['berry'])) {
                $versionGroupSourceData['berry'] = $this->transformBerry($versionGroupSourceData['berry'], $versionGroupDestinationData->getBerry());
            }
            if (isset($versionGroupSourceData['machine'])) {
                $versionGroupSourceData['machine'] = $this->transformMachine($versionGroupSourceData['machine'], $versionGroupDestinationData->getMachine(), $versionGroup);
            }

            /** @var ItemInVersionGroup $versionGroupDestinationData */
            $versionGroupDestinationData = $this->mergeProperties($versionGroupSourceData, $versionGroupDestinationData);
            $destinationData->addChild($versionGroupDestinationData);
        }

        return $destinationData;
    }

    /**
     * @param array      $berrySourceData
     * @param Berry|null $berryDestinationData
     *
     * @return Berry
     * @throws \DragoonBoots\A2B\Exception\NoMappingForIdsException
     * @throws \DragoonBoots\A2B\Exception\NonexistentDriverException
     * @throws \DragoonBoots\A2B\Exception\NonexistentMigrationException
     */
    protected function transformBerry(array $berrySourceData, ?Berry $berryDestinationData): Berry
    {
        if (!$berryDestinationData) {
            $berryDestinationData = new Berry();
        }

        $berrySourceData['firmness'] = $this->referenceStore->get(BerryFirmness::class, ['identifier' => $berrySourceData['firmness']]);
        if (isset($berrySourceData['natural_gift_type'])) {
            $berrySourceData['natural_gift_type'] = $this->referenceStore->get(Type::class, ['identifier' => $berrySourceData['natural_gift_type']]);
        }

        $berryFlavors = $berryDestinationData->getFlavors();
        foreach ($berrySourceData['flavors'] as $flavor => $level) {
            /** @var \App\Entity\BerryFlavor $flavor */
            $flavor = $this->referenceStore->get(BerryFlavor::class, ['identifier' => $flavor]);
            $flavorLevel = null;
            foreach ($berryFlavors as $checkFlavorLevel) {
                if ($checkFlavorLevel->getFlavor() === $flavor) {
                    $flavorLevel = $checkFlavorLevel;
                    break;
                }
            }
            if (!$flavorLevel) {
                $flavorLevel = new BerryFlavorLevel();
            }
            $flavorLevel->setFlavor($flavor)->setLevel($level);
            $berryDestinationData->addFlavor($flavorLevel);
        }
        unset($berrySourceData['flavors']);

        $berrySourceData['harvest'] = Range::fromString($berrySourceData['harvest']);

        $berrySourceData['size'] = new Length($berrySourceData['size'], 'mm');
        $berrySourceData['growth_time'] = new Time($berrySourceData['growth_time'], 'hours');

        /** @var Berry $berryDestinationData */
        $berryDestinationData = $this->mergeProperties($berrySourceData, $berryDestinationData);

        return $berryDestinationData;
    }

    /**
     * @param array        $machineSourceData
     * @param Machine|null $machineDestinationData
     * @param VersionGroup $versionGroup
     *
     * @return Machine
     * @throws \DragoonBoots\A2B\Exception\NoMappingForIdsException
     * @throws \DragoonBoots\A2B\Exception\NonexistentDriverException
     * @throws \DragoonBoots\A2B\Exception\NonexistentMigrationException
     */
    protected function transformMachine(array $machineSourceData, ?Machine $machineDestinationData, VersionGroup $versionGroup): Machine
    {
        if (!$machineDestinationData) {
            $machineDestinationData = new Machine();
        }

        /** @var \App\Entity\Move $move */
        $move = $this->referenceStore->get(Move::class, ['identifier' => $machineSourceData['move']]);
        $machineSourceData['move'] = $move->findChildByGrouping($versionGroup);

        /** @var Machine $machineDestinationData */
        $machineDestinationData = $this->mergeProperties($machineSourceData, $machineDestinationData);

        return $machineDestinationData;
    }

    /**
     * {@inheritdoc}
     */
    public function defaultResult()
    {
        return new \App\Entity\Item();
    }
}
