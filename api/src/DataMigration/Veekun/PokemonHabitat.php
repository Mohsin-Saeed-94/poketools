<?php

namespace App\DataMigration\Veekun;

use DragoonBoots\A2B\Annotations\DataMigration;
use DragoonBoots\A2B\Annotations\IdField;
use DragoonBoots\A2B\DataMigration\AbstractDataMigration;
use DragoonBoots\A2B\DataMigration\DataMigrationInterface;
use DragoonBoots\A2B\Drivers\Source\DbalSourceDriver;
use DragoonBoots\A2B\Drivers\SourceDriverInterface;

/**
 * Pokemon Habitat migration.
 *
 * @DataMigration(
 *     name="Pokemon Habitat",
 *     group="Veekun",
 *     source="veekun",
 *     sourceIds={@IdField(name="id")},
 *     destination="csv:///%kernel.project_dir%/resources/data/pokemon_habitat.csv",
 *     destinationIds={@IdField(name="identifier", type="string")}
 * )
 */
class PokemonHabitat extends AbstractDataMigration implements DataMigrationInterface
{

    /**
     * {@inheritdoc}
     * @param DbalSourceDriver $sourceDriver
     */
    public function configureSource(SourceDriverInterface $sourceDriver)
    {
        $statement = $sourceDriver->getConnection()->prepare(
            <<<SQL
SELECT "pokemon_habitats"."id",
       "pokemon_habitats"."identifier",
       "pokemon_habitat_names"."name"
FROM "pokemon_habitats"
     JOIN "pokemon_habitat_names" ON "pokemon_habitats"."id" = "pokemon_habitat_names"."pokemon_habitat_id"
WHERE "pokemon_habitat_names"."local_language_id" = 9;
SQL
        );
        $sourceDriver->setStatement($statement);

        $countStatement = $sourceDriver->getConnection()->prepare(
            <<<SQL
SELECT count(*)
FROM "pokemon_habitats";
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
        $destinationData = array_merge($sourceData, $destinationData);

        return $destinationData;
    }
}
