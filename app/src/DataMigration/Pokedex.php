<?php

namespace App\DataMigration;

use DragoonBoots\A2B\Annotations\DataMigration;
use DragoonBoots\A2B\Annotations\IdField;
use DragoonBoots\A2B\DataMigration\DataMigrationInterface;

/**
 * Pokedex migration.
 *
 * @DataMigration(
 *     name="Pokedex",
 *     source="/%kernel.project_dir%/resources/data/pokedex",
 *     sourceDriver="DragoonBoots\A2B\Drivers\Source\YamlSourceDriver",
 *     sourceIds={@IdField(name="identifier", type="string")},
 *     destination="\App\Entity\Pokedex",
 *     destinationDriver="DragoonBoots\A2B\Drivers\Destination\DoctrineDestinationDriver",
 *     destinationIds={@IdField(name="id")},
 *     depends={"App\DataMigration\VersionGroup"}
 * )
 */
class Pokedex extends AbstractDoctrineDataMigration implements DataMigrationInterface
{

    /**
     * {@inheritdoc}
     * @param \App\Entity\Pokedex $destinationData
     */
    public function transform($sourceData, $destinationData)
    {
        // Ensure the national dex gets the correct slug.
        if ($sourceData['identifier'] == 'national') {
            $destinationData->setSlug($sourceData['identifier']);
        }
        unset($sourceData['identifier']);
        foreach ($sourceData['version_groups'] as &$versionGroup) {
            $versionGroup = $this->referenceStore->get(VersionGroup::class, ['identifier' => $versionGroup]);
        }
        $destinationData = $this->mergeProperties($sourceData, $destinationData);

        return $destinationData;
    }

    /**
     * {@inheritdoc}
     */
    public function defaultResult()
    {
        return new \App\Entity\Pokedex();
    }
}
