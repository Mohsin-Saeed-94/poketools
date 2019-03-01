<?php

namespace App\DataMigration;

use DragoonBoots\A2B\Annotations\DataMigration;
use DragoonBoots\A2B\Annotations\IdField;
use DragoonBoots\A2B\DataMigration\DataMigrationInterface;

/**
 * Type migration.
 *
 * @DataMigration(
 *     name="Type",
 *     source="csv:///%kernel.project_dir%/resources/data/type.csv",
 *     sourceIds={@IdField(name="identifier", type="string")},
 *     destination="doctrine:///App/Entity/Type",
 *     destinationIds={@IdField(name="id")},
 *     depends={"App\DataMigration\MoveDamageClass"}
 * )
 */
class Type extends AbstractDoctrineDataMigration implements DataMigrationInterface
{

    /**
     * {@inheritdoc}
     * @param \App\Entity\Type $destinationData
     */
    public function transform($sourceData, $destinationData)
    {
        static $position = 0;
        $destinationData->setPosition($position);
        $position++;

        $destinationData->setSlug($sourceData['identifier']);
        unset($sourceData['identifier']);

        if ($sourceData['damage_class']) {
            $sourceData['damage_class'] = $this->referenceStore->get(MoveDamageClass::class, ['identifier' => $sourceData['damage_class']]);
        } else {
            unset($sourceData['damage_class']);
        }
        $this->mergeProperties($sourceData, $destinationData);

        return $destinationData;
    }

    /**
     * {@inheritdoc}
     */
    public function defaultResult()
    {
        return new \App\Entity\Type();
    }
}
