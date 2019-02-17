<?php

namespace App\DataMigration\Veekun;

use DragoonBoots\A2B\Annotations\DataMigration;
use DragoonBoots\A2B\Annotations\IdField;
use DragoonBoots\A2B\DataMigration\AbstractDataMigration;
use DragoonBoots\A2B\DataMigration\DataMigrationInterface;
use DragoonBoots\A2B\Drivers\Source\DbalSourceDriver;
use DragoonBoots\A2B\Drivers\SourceDriverInterface;

/**
 * Move Category migration.
 *
 * This does some hairy transformations on the veekun data to normalize it.
 * Veekun stores a separate row for each combination of categories.  As migrated,
 * these combinations are broken up.
 *
 * As a consequence of this, descriptions are not migrated from Veekun and must
 * be entered manually.
 *
 * @DataMigration(
 *     name="Move Category",
 *     group="Veekun",
 *     source="veekun",
 *     sourceIds={@IdField(name="identifier", type="string")},
 *     destination="csv:///%kernel.project_dir%/resources/data/move_category.csv",
 *     destinationIds={@IdField(name="identifier", type="string")}
 * )
 */
class MoveCategory extends AbstractDataMigration implements DataMigrationInterface
{

    /**
     * {@inheritdoc}
     * @param DbalSourceDriver $sourceDriver
     */
    public function configureSource(SourceDriverInterface $sourceDriver)
    {
        $sourceDriver->setStatement(
            <<<SQL
WITH RECURSIVE "identifier_explode"("cat_id", "identifier", "rest") AS (SELECT "id", '', "identifier" || '+'
                                                                        FROM "move_meta_categories"
                                                                        WHERE "id"
    UNION ALL
    SELECT "cat_id",
           substr("rest", 0, instr("rest", '+')),
           substr("rest", instr("rest", '+') + 1)
    FROM "identifier_explode"
    WHERE "rest" <> '')
SELECT DISTINCT "identifier_explode"."identifier"
FROM "move_meta_categories"
     JOIN "identifier_explode"
         ON "identifier_explode"."cat_id" = "move_meta_categories"."id"
WHERE "identifier_explode"."identifier" <> ''
ORDER BY "move_meta_categories"."id";
SQL
        );

        $sourceDriver->setCountStatement(
            <<<SQL
WITH RECURSIVE "identifier_explode"("cat_id", "identifier", "rest") AS (SELECT "id", '', "identifier" || '+'
                                                                        FROM "move_meta_categories"
                                                                        WHERE "id"
    UNION ALL
    SELECT "cat_id",
           substr("rest", 0, instr("rest", '+')),
           substr("rest", instr("rest", '+') + 1)
    FROM "identifier_explode"
    WHERE "rest" <> '')
SELECT count(DISTINCT "identifier_explode"."identifier")
FROM "move_meta_categories"
     JOIN "identifier_explode"
         ON "identifier_explode"."cat_id" = "move_meta_categories"."id"
WHERE "identifier_explode"."identifier" <> '';
SQL
        );
    }

    /**
     * {@inheritdoc}
     */
    public function transform($sourceData, $destinationData)
    {
        $sourceData['name'] = str_replace('-', ' ', $sourceData['identifier']);
        $sourceData['name'] = ucwords($sourceData['name']);
        $sourceData['description'] = null;
        $destinationData = array_merge($sourceData, $destinationData);

        return $destinationData;
    }
}
