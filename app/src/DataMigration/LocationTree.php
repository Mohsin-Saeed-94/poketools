<?php

namespace App\DataMigration;

use App\Entity\LocationInVersionGroup;
use App\Entity\VersionGroup;
use DragoonBoots\A2B\Annotations\DataMigration;
use DragoonBoots\A2B\Annotations\IdField;
use DragoonBoots\A2B\DataMigration\DataMigrationInterface;

/**
 * Location tree migration.
 *
 * This will migrate location tree relationships (e.g. the Tin Tower is in Ecruteak City)
 *
 * @DataMigration(
 *     name="Location Tree",
 *     source="yaml:///%kernel.project_dir%/resources/data/location",
 *     sourceIds={@IdField(name="identifier", type="string")},
 *     destination="doctrine:///App/Entity/Location",
 *     destinationIds={@IdField(name="id")},
 *     depends={"App\DataMigration\Location"},
 *     extends="App\DataMigration\Location"
 * )
 */
class LocationTree extends AbstractDoctrineDataMigration implements DataMigrationInterface
{

    /**
     * {@inheritdoc}
     * @param                            $sourceData
     * @param \App\Entity\Location $destinationData
     *
     * @return \App\Entity\Location|null
     * @throws \DragoonBoots\A2B\Exception\NoMappingForIdsException
     * @throws \DragoonBoots\A2B\Exception\NonexistentDriverException
     * @throws \DragoonBoots\A2B\Exception\NonexistentMigrationException
     */
    public function transform($sourceData, $destinationData)
    {
        unset($sourceData['identifier']);
        $changed = false;
        foreach ($sourceData as $versionGroup => $versionGroupData) {
            /** @var \App\Entity\VersionGroup $versionGroup */
            $versionGroup = $this->referenceStore->get(
                \App\DataMigration\VersionGroup::class,
                ['identifier' => $versionGroup]
            );
            $locationInVersionGroup = $destinationData->findChildByGrouping($versionGroup);
            if (!$this->transformVersionGroup($versionGroupData, $locationInVersionGroup, $versionGroup)) {
                continue;
            }
            $changed = true;
        }

        if ($changed) {
            // Only return an entity if changes have been made.  This saves on
            // ORM overhead.

            return $destinationData;
        }

        return null;
    }

    /**
     * @param array $sourceData
     * @param LocationInVersionGroup $destinationData
     * @param VersionGroup $versionGroup
     *
     * @return LocationInVersionGroup|null
     * @throws \DragoonBoots\A2B\Exception\NoMappingForIdsException
     * @throws \DragoonBoots\A2B\Exception\NonexistentDriverException
     * @throws \DragoonBoots\A2B\Exception\NonexistentMigrationException
     */
    protected function transformVersionGroup(
        array $sourceData,
        LocationInVersionGroup $destinationData,
        VersionGroup $versionGroup
    ): ?LocationInVersionGroup {
        if (!isset($sourceData['super'])) {
            // No super location
            return null;
        }

        /** @var \App\Entity\Location $superLocation */
        $superLocation = $this->referenceStore->get(Location::class, ['identifier' => $sourceData['super']]);
        $superLocation = $superLocation->findChildByGrouping($versionGroup);
        if (!$superLocation) {
            throw new \DomainException(
                sprintf(
                    '"%s" does not exist in the "%s" version group.',
                    $sourceData['super'],
                    $versionGroup->getName()
                )
            );
        }
        $destinationData->setSuperLocation($superLocation);

        return $destinationData;
    }

    /**
     * Throw an exception when encountering a Location that doesn't exist when
     * it should.
     */
    protected function nonexistentLocation()
    {
        throw new \LogicException('The destination must already exist for Location trees');
    }
}
