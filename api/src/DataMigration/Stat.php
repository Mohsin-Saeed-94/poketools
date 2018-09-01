<?php

namespace App\DataMigration;

use DragoonBoots\A2B\Annotations\DataMigration;
use DragoonBoots\A2B\Annotations\IdField;
use DragoonBoots\A2B\DataMigration\DataMigrationInterface;

/**
 * Stat migration.
 *
 * @DataMigration(
 *     name="Stat",
 *     source="csv:///%kernel.project_dir%/resources/data/stat.csv",
 *     sourceIds={@IdField(name="identifier", type="string")},
 *     destination="doctrine:///App/Entity/Stat",
 *     destinationIds={@IdField(name="id")},
 *     depends={"App\DataMigration\MoveDamageClass"}
 * )
 */
class Stat extends AbstractDoctrineDataMigration implements DataMigrationInterface
{

    /**
     * {@inheritdoc}
     * @param \App\Entity\Stat $destinationData
     */
    public function transform($sourceData, $destinationData)
    {
        unset($sourceData['identifier']);
        static $position = 1;
        $sourceData['position'] = $position;
        $position++;
        if ($sourceData['damage_class']) {
            $sourceData['damage_class'] = $this->referenceStore->get(MoveDamageClass::class, ['identifier' => $sourceData['damage_class']]);
        } else {
            $sourceData['damage_class'] = null;
        }

        $properties = array_keys($sourceData);
        $destinationData = $this->mergeProperties($properties, $sourceData, $destinationData);

        return $destinationData;
    }

    /**
     * {@inheritdoc}
     */
    public function defaultResult()
    {
        return new \App\Entity\Stat();
    }
}
