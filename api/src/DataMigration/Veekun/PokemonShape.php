<?php

namespace App\DataMigration\Veekun;

use DragoonBoots\A2B\Annotations\DataMigration;
use DragoonBoots\A2B\Annotations\IdField;
use DragoonBoots\A2B\DataMigration\AbstractDataMigration;
use DragoonBoots\A2B\DataMigration\DataMigrationInterface;
use DragoonBoots\A2B\Drivers\Source\DbalSourceDriver;
use DragoonBoots\A2B\Drivers\SourceDriverInterface;

/**
 * Pokemon Shape migration.
 *
 * @DataMigration(
 *     name="Pokemon Shape",
 *     group="Veekun",
 *     source="veekun",
 *     sourceIds={@IdField(name="version_group_id"), @IdField(name="id")},
 *     destination="csv:///%kernel.project_dir%/resources/data/pokemon_shape.csv",
 *     destinationIds={@IdField(name="version_group", type="string"), @IdField(name="identifier", type="string")}
 * )
 */
class PokemonShape extends AbstractDataMigration implements DataMigrationInterface
{

    /**
     * {@inheritdoc}
     * @param DbalSourceDriver $sourceDriver
     */
    public function configureSource(SourceDriverInterface $sourceDriver)
    {
        $statement = $sourceDriver->getConnection()->prepare(
            <<<SQL
SELECT "version_groups"."id" AS "version_group_id",
       "version_groups"."identifier" AS "version_group",
       "pokemon_shapes"."id",
       "pokemon_shapes"."identifier",
       "pokemon_shape_prose"."name",
       "pokemon_shape_prose"."awesome_name" AS "taxonomy_name",
       "pokemon_shape_prose"."description"
FROM "pokemon_shapes",
     "version_groups"
     JOIN "pokemon_shape_prose" ON "pokemon_shapes"."id" = "pokemon_shape_prose"."pokemon_shape_id"
     JOIN "generations" ON "version_groups"."generation_id" = "generations"."id"
WHERE "generations"."id" >= 4 AND "pokemon_shape_prose"."local_language_id" = 9
ORDER BY "version_groups"."order" ASC, "pokemon_shapes"."id" ASC;
SQL
        );
        $sourceDriver->setStatement($statement);

        $countStatement = $sourceDriver->getConnection()->prepare(
            <<<SQL
SELECT count(*)
FROM "pokemon_shapes",
     "version_groups"
     JOIN "generations" ON "version_groups"."generation_id" = "generations"."id"
WHERE "generations"."id" >= 4;
SQL
        );
        $sourceDriver->setCountStatement($countStatement);
    }

    /**
     * {@inheritdoc}
     */
    public function transform($sourceData, $destinationData)
    {
        foreach (['version_group_id', 'id'] as $removeKey) {
            unset($sourceData[$removeKey]);
        }
        $destinationData = array_merge($sourceData, $destinationData);

        if (!isset($destinationData['icon'])) {
            $destinationData['icon'] = sprintf('%s.png', $sourceData['identifier']);
        }

        return $destinationData;
    }
}
