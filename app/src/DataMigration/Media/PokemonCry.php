<?php

namespace App\DataMigration\Media;


use DragoonBoots\A2B\Annotations\DataMigration;
use DragoonBoots\A2B\Annotations\IdField;
use DragoonBoots\A2B\Drivers\Source\DbalSourceDriver;
use DragoonBoots\A2B\Drivers\SourceDriverInterface;
use Symfony\Component\Process\Process;

/**
 * Pokemon cry migration.
 *
 * @DataMigration(
 *     name="Pokemon Cry",
 *     group="Media",
 *     source="veekun",
 *     sourceIds={@IdField(name="group", type="string"), @IdField(name="species_id"), @IdField(name="form_id")},
 *     destination="file:///%kernel.project_dir%/assets/static/pokemon/cry",
 *     destinationIds={@IdField(name="group", type="string"), @IdField(name="id", type="string")}
 * )
 */
class PokemonCry extends AbstractMediaMigration
{
    use PokemonMediaTrait;

    private const GROUP_MAP = [
        '' => '',
        'old' => 'gen5',
    ];

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
SELECT 'old'                          AS "group",
       "pokemon_species"."id"         AS "species_id",
       "pokemon_species"."identifier" AS "species_identifier",
       "pokemon_forms"."id"           AS "form_id",
       "pokemon_forms"."form_identifier"
FROM "pokemon_forms"
       JOIN "pokemon" ON "pokemon_forms"."pokemon_id" = "pokemon"."id"
       JOIN "pokemon_species" ON "pokemon"."species_id" = "pokemon_species"."id"
       JOIN "version_groups" ON "pokemon_forms"."introduced_in_version_group_id" = "version_groups"."id"
WHERE "pokemon_species"."generation_id" <= 5
  AND "version_groups"."generation_id" <= 5
ORDER BY "group", "species_id", "form_id";
SQL
        );

        $sourceDriver->setCountStatement(
            <<<SQL
SELECT "pokemon_forms_count"."count" + "old_pokemon_forms_count"."count"
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
       WHERE "pokemon_species"."generation_id" <= 5
         AND "version_groups"."generation_id" <= 5
     ) "old_pokemon_forms_count";
SQL
        );
    }

    /**
     * {@inheritdoc}
     */
    public function transform($sourceData, $destinationData)
    {
        $oldFilename = $this->buildFilename($sourceData['species_id'], $sourceData['form_identifier'], 'ogg');
        $newFilename = $sourceData['species_identifier'].'-'.($sourceData['form_identifier'] ?? 'default').'.webm';
        $newGroup = self::GROUP_MAP[$sourceData['group']];
        if (!empty($sourceData['group'])) {
            $oldFilename = $sourceData['group'].'/'.$oldFilename;
            $newFilename = $newGroup.'/'.$newFilename;
        }
        $oldPath = $this->resourcesDir.'/pokedex-media/pokemon/cries/'.$oldFilename;
        $newPath = $this->destinationPath.'/'.$newFilename;

        if (!is_file($newPath)) {
            if (is_file($oldPath)) {
                if (!is_dir(dirname($newPath))) {
                    mkdir(dirname($newPath), 0755, true);
                }
                $cmd = [
                    'ffmpeg',
                    '-i',
                    $oldPath,
                    '-codec:a',
                    'copy',
                    $newPath,
                ];
                $process = new Process($cmd);
                $process->mustRun();
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
            'group' => $newGroup,
            'id' => $newFilename,
        ];
    }
}