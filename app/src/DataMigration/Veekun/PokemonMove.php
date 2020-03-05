<?php

namespace App\DataMigration\Veekun;

use DragoonBoots\A2B\Annotations\DataMigration;
use DragoonBoots\A2B\Annotations\IdField;
use DragoonBoots\A2B\DataMigration\AbstractDataMigration;
use DragoonBoots\A2B\DataMigration\DataMigrationInterface;
use DragoonBoots\A2B\Drivers\DestinationDriverInterface;
use DragoonBoots\A2B\Drivers\Source\DbalSourceDriver;
use DragoonBoots\A2B\Drivers\SourceDriverInterface;

/**
 * Pokemon Moves migration.
 *
 * @DataMigration(
 *     name="Pokemon Moves",
 *     group="Veekun",
 *     source="veekun",
 *     sourceIds={
 *         @IdField(name="pokemon", type="string"),
 *         @IdField(name="version_group", type="string"),
 *         @IdField(name="move", type="string"),
 *         @IdField(name="learn_method", type="string")
 *     },
 *     destination="/%kernel.project_dir%/resources/data/pokemon_move.csv",
 *     destinationDriver="DragoonBoots\A2B\Drivers\Destination\CsvDestinationDriver",
 *     destinationIds={
 *         @IdField(name="pokemon", type="string"),
 *         @IdField(name="version_group", type="string"),
 *         @IdField(name="move", type="string"),
 *         @IdField(name="learn_method", type="string")
 *     }
 * )
 */
class PokemonMove extends AbstractDataMigration implements DataMigrationInterface
{

    /**
     * A list of version groups with no HMs.
     *
     * @var array
     */
    protected $noHms = ['sun-moon', 'ultra-sun-ultra-moon'];

    /**
     * {@inheritdoc}
     * @param DbalSourceDriver $sourceDriver
     */
    public function configureSource(SourceDriverInterface $sourceDriver)
    {
        $sourceDriver->setStatement(
            <<<SQL
SELECT "pokemon_species"."identifier" AS "species",
       "pokemon"."identifier" AS "pokemon",
       "version_groups"."identifier" AS "version_group",
       "moves"."identifier" AS "move",
       "pokemon_move_methods"."identifier" AS "learn_method",
       nullif("pokemon_moves"."level", 0) AS "level",
       CASE
           WHEN "pokemon_move_methods"."identifier" = 'machine' THEN
               "items"."identifier"
       END AS "machine"
FROM "pokemon_moves"
     JOIN "pokemon"
          ON "pokemon_moves"."pokemon_id" = "pokemon"."id"
     JOIN "pokemon_species"
          ON "pokemon"."species_id" = "pokemon_species"."id"
     JOIN "version_groups"
          ON "pokemon_moves"."version_group_id" = "version_groups"."id"
     JOIN "moves"
          ON "pokemon_moves"."move_id" = "moves"."id"
     JOIN "pokemon_move_methods"
          ON "pokemon_moves"."pokemon_move_method_id" = "pokemon_move_methods"."id"
     LEFT OUTER JOIN "machines"
                     ON "moves"."id" = "machines"."move_id" AND "version_groups"."id" = "machines"."version_group_id"
     LEFT OUTER JOIN "items"
                     ON "machines"."item_id" = "items"."id"
ORDER BY "pokemon"."order",
         "version_groups"."order",
         "pokemon_move_methods"."id",
         "level",
         "machines"."machine_number",
         "pokemon_moves"."order",
         "moves"."id";
SQL
        );

        $sourceDriver->setCountStatement(
            <<<SQL
SELECT count(*)
FROM "pokemon_moves";
SQL
        );
    }

    /**
     * {@inheritdoc}
     */
    public function transform($sourceData, $destinationData)
    {
        if (in_array($sourceData['version_group'], $this->noHms)
            && $sourceData['machine']
            && strpos($sourceData['machine'], 'hm') !== false) {
            // Veekun has data for HM moves in games without HMs.  Skip these.
            return null;
        }

        $destinationData = array_merge($sourceData, $destinationData);

        return $destinationData;
    }
}
