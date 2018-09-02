<?php

namespace App\DataMigration\Veekun;

use DragoonBoots\A2B\Annotations\DataMigration;
use DragoonBoots\A2B\Annotations\IdField;
use DragoonBoots\A2B\DataMigration\AbstractDataMigration;
use DragoonBoots\A2B\DataMigration\DataMigrationInterface;
use DragoonBoots\A2B\Drivers\Source\DbalSourceDriver;
use DragoonBoots\A2B\Drivers\SourceDriverInterface;

/**
 * Item Flag migration.
 *
 * @DataMigration(
 *     name="Item Flag",
 *     group="Veekun",
 *     source="veekun",
 *     sourceIds={@IdField(name="id")},
 *     destination="csv:///%kernel.project_dir%/resources/data/item_flag.csv",
 *     destinationIds={@IdField(name="identifier", type="string")}
 * )
 */
class ItemFlag extends AbstractDataMigration implements DataMigrationInterface
{

    /**
     * {@inheritdoc}
     * @param DbalSourceDriver $sourceDriver
     */
    public function configureSource(SourceDriverInterface $sourceDriver)
    {
        $statement = $sourceDriver->getConnection()->prepare(
            <<<SQL
SELECT "item_flags"."id",
       "item_flags"."identifier",
       "item_flag_prose"."name",
       "item_flag_prose"."description"
FROM "item_flags"
     JOIN "item_flag_prose"
         ON "item_flags"."id" = "item_flag_prose"."item_flag_id"
WHERE "item_flag_prose"."local_language_id" = 9;
SQL
        );
        $sourceDriver->setStatement($statement);

        $countStatement = $sourceDriver->getConnection()->prepare(
            <<<SQL
SELECT count(*)
FROM "item_flags";
SQL
        );
        $sourceDriver->setCountStatement($countStatement);
    }

    /**
     * {@inheritdoc}
     */
    public function transform($sourceData, $destinationData)
    {
        unset($sourceData['id']);
        $sourceData['name'] = $this->fixupName($sourceData['name']);

        $destinationData = array_merge($sourceData, $destinationData);

        return $destinationData;
    }

    /**
     * Modify the name to remove underscores in a nice way
     *
     * @param $name
     *
     * @return string
     */
    protected function fixupName($name): string
    {
        $firstUnderscore = strpos($name, '_');
        if ($firstUnderscore !== false) {
            $before = substr($name, 0, $firstUnderscore);
            $after = substr($name, $firstUnderscore + 1);
            $after = str_replace('_', ' ', $after);
            $name = $before.' ('.$after.')';
        }

        return $name;
    }
}
