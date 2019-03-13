<?php

namespace App\DataMigration\Media;


use DragoonBoots\A2B\Annotations\DataMigration;
use DragoonBoots\A2B\Annotations\IdField;
use DragoonBoots\A2B\Drivers\Source\DbalSourceDriver;
use DragoonBoots\A2B\Drivers\SourceDriverInterface;

/**
 * Pokemon art migration.
 *
 * @DataMigration(
 *     name="Pokemon Art",
 *     group="Media",
 *     source="veekun",
 *     sourceIds={@IdField(name="group", type="string"), @IdField(name="species_id"), @IdField(name="form_id")},
 *     destination="file:///%kernel.project_dir%/assets/static/pokemon/art",
 *     destinationIds={@IdField(name="group", type="string"), @IdField(name="id", type="string")}
 * )
 */
class PokemonArt extends AbstractMediaMigration
{
    use PokemonMediaTrait;

    /**
     * {@inheritdoc}
     *
     * @param DbalSourceDriver $sourceDriver
     */
    public function configureSource(SourceDriverInterface $sourceDriver)
    {
        $sourceDriver->setStatement(
            <<<SQL
SELECT ''                             AS "group",
       "pokemon_species"."id"         AS "species_id",
       "pokemon_species"."identifier" AS "species_identifier",
       "pokemon_forms"."id"           AS "form_id",
       "pokemon_forms"."form_identifier"
FROM "pokemon_forms"
       JOIN "pokemon" ON "pokemon_forms"."pokemon_id" = "pokemon"."id"
       JOIN "pokemon_species" ON "pokemon"."species_id" = "pokemon_species"."id"
UNION
SELECT 'female'                       AS "group",
       "pokemon_species"."id"         AS "species_id",
       "pokemon_species"."identifier" AS "species_identifier",
       "pokemon_forms"."id"           AS "form_id",
       "pokemon_forms"."form_identifier"
FROM "pokemon_forms"
       JOIN "pokemon" ON "pokemon_forms"."pokemon_id" = "pokemon"."id"
       JOIN "pokemon_species" ON "pokemon"."species_id" = "pokemon_species"."id"
       JOIN "version_groups" ON "pokemon_forms"."introduced_in_version_group_id" = "version_groups"."id"
WHERE "pokemon_species"."generation_id" >= 4
ORDER BY "group", "species_id", "form_id";
SQL
        );

        $sourceDriver->setCountStatement(
            <<<SQL
SELECT "pokemon_forms_count"."count" + "female_pokemon_forms_count"."count"
FROM (
       SELECT count(*) AS "count"
       FROM "pokemon_forms"
     ) "pokemon_forms_count",
     (
       SELECT count(*) AS "count"
       FROM "pokemon_forms"
              JOIN "pokemon" ON "pokemon_forms"."pokemon_id" = "pokemon"."id"
              JOIN "pokemon_species" ON "pokemon"."species_id" = "pokemon_species"."id"
              JOIN "version_groups" ON "pokemon_forms"."introduced_in_version_group_id" = "version_groups"."id"
       WHERE "pokemon_species"."generation_id" >= 4
     ) "female_pokemon_forms_count";
SQL
        );
    }

    /**
     * {@inheritdoc}
     */
    public function transform($sourceData, $destinationData)
    {
        $oldFilename = $this->buildFilename($sourceData['species_id'], $sourceData['form_identifier'], 'png');
        $newFilename = $sourceData['species_identifier'].'-'.($sourceData['form_identifier'] ?? 'default').'.png';
        if (!empty($sourceData['group'])) {
            $oldFilename = $sourceData['group'].'/'.$oldFilename;
            $newFilename = $sourceData['group'].'/'.$newFilename;
        }
        $oldPath = $this->resourcesDir.'/pokedex-media/pokemon/sugimori/'.$oldFilename;
        $newPath = $this->destinationPath.'/'.$newFilename;

        if (!is_file($newPath)) {
            if (is_file($oldPath)) {
                if (!is_dir(dirname($newPath))) {
                    mkdir(dirname($newPath), 0755, true);
                }
                copy($oldPath, $newPath);
            } else {
                // Path doesn't exist
                $missingLogPath = $this->destinationPath.'/missing.csv';
                if (!is_file($missingLogPath)) {
                    $handle = fopen($missingLogPath, 'wb');
                    fputcsv($handle, ['group', 'species_id', 'species_identifier', 'form_identifier']);
                } else {
                    $handle = fopen($missingLogPath, 'ab');
                }
                fputcsv(
                    $handle,
                    [
                        $sourceData['group'],
                        $sourceData['species_id'],
                        $sourceData['species_identifier'],
                        $sourceData['form_identifier'] ?? 'NULL',
                    ]
                );
                fclose($handle);

                return null;
            }
        }

        return [
            'group' => $sourceData['group'],
            'id' => $newFilename,
        ];
    }
}