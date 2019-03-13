<?php

namespace App\DataMigration\Media;


use DragoonBoots\A2B\Annotations\DataMigration;
use DragoonBoots\A2B\Annotations\IdField;
use DragoonBoots\A2B\Drivers\Source\DbalSourceDriver;
use DragoonBoots\A2B\Drivers\SourceDriverInterface;
use Symfony\Component\Process\Process;

/**
 * Pokemon Sprite migration.
 *
 * @DataMigration(
 *     name="Pokemon Sprite",
 *     group="Media",
 *     source="veekun",
 *     sourceIds={
 *         @IdField(name="version_group", type="string"),
 *         @IdField(name="group", type="string"),
 *         @IdField(name="species_id"),
 *         @IdField(name="form_id")
 *     },
 *     destination="file:///%kernel.project_dir%/assets/static/pokemon/sprite",
 *     destinationIds={
 *         @IdField(name="version_group", type="string"),
 *         @IdField(name="id", type="string")
 *     }
 * )
 */
class PokemonSprite extends AbstractMediaMigration
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
SELECT "version_groups"."identifier"  AS "version_group",
       ''                             AS "group",
       "pokemon_species"."id"         AS "species_id",
       "pokemon_species"."identifier" AS "species_identifier",
       "pokemon_forms"."id"           AS "form_id",
       "pokemon_forms"."form_identifier",
       "version_groups"."order"       AS "version_group_order"
FROM "pokemon_forms"
       JOIN "version_groups"
       JOIN "pokemon" ON "pokemon_forms"."pokemon_id" = "pokemon"."id"
       JOIN "pokemon_species" ON "pokemon"."species_id" = "pokemon_species"."id"
WHERE "version_groups"."order" >= "pokemon_forms"."introduced_in_version_group_id"
UNION
SELECT "version_groups"."identifier"  AS "version_group",
       'back'                         AS "group",
       "pokemon_species"."id"         AS "species_id",
       "pokemon_species"."identifier" AS "species_identifier",
       "pokemon_forms"."id"           AS "form_id",
       "pokemon_forms"."form_identifier",
       "version_groups"."order"       AS "version_group_order"
FROM "pokemon_forms"
       JOIN "version_groups"
       JOIN "pokemon" ON "pokemon_forms"."pokemon_id" = "pokemon"."id"
       JOIN "pokemon_species" ON "pokemon"."species_id" = "pokemon_species"."id"
WHERE "version_groups"."order" >= "pokemon_forms"."introduced_in_version_group_id"
  -- Gen 6 eliminated back sprites with the switch to 3D
  AND "version_groups"."generation_id" < 6
UNION
SELECT "version_groups"."identifier"  AS "version_group",
       'back/female'                  AS "group",
       "pokemon_species"."id"         AS "species_id",
       "pokemon_species"."identifier" AS "species_identifier",
       "pokemon_forms"."id"           AS "form_id",
       "pokemon_forms"."form_identifier",
       "version_groups"."order"       AS "version_group_order"
FROM "pokemon_forms"
       JOIN "version_groups"
       JOIN "pokemon" ON "pokemon_forms"."pokemon_id" = "pokemon"."id"
       JOIN "pokemon_species" ON "pokemon"."species_id" = "pokemon_species"."id"
WHERE "version_groups"."order" >= "pokemon_forms"."introduced_in_version_group_id"
  AND "version_groups"."generation_id" < 6
  AND "version_groups"."generation_id" >= 4
UNION
SELECT "version_groups"."identifier"  AS "version_group",
       'back/shiny'                   AS "group",
       "pokemon_species"."id"         AS "species_id",
       "pokemon_species"."identifier" AS "species_identifier",
       "pokemon_forms"."id"           AS "form_id",
       "pokemon_forms"."form_identifier",
       "version_groups"."order"       AS "version_group_order"
FROM "pokemon_forms"
       JOIN "version_groups"
       JOIN "pokemon" ON "pokemon_forms"."pokemon_id" = "pokemon"."id"
       JOIN "pokemon_species" ON "pokemon"."species_id" = "pokemon_species"."id"
WHERE "version_groups"."order" >= "pokemon_forms"."introduced_in_version_group_id"
  AND "version_groups"."generation_id" < 6
  AND "version_groups"."generation_id" >= 4
UNION
SELECT "version_groups"."identifier"  AS "version_group",
       'gray'                         AS "group",
       "pokemon_species"."id"         AS "species_id",
       "pokemon_species"."identifier" AS "species_identifier",
       "pokemon_forms"."id"           AS "form_id",
       "pokemon_forms"."form_identifier",
       "version_groups"."order"       AS "version_group_order"
FROM "pokemon_forms"
       JOIN "version_groups"
       JOIN "pokemon" ON "pokemon_forms"."pokemon_id" = "pokemon"."id"
       JOIN "pokemon_species" ON "pokemon"."species_id" = "pokemon_species"."id"
WHERE "version_groups"."order" >= "pokemon_forms"."introduced_in_version_group_id"
  AND "version_groups"."generation_id" = 1
UNION
SELECT "version_groups"."identifier"  AS "version_group",
       'gbc'                          AS "group",
       "pokemon_species"."id"         AS "species_id",
       "pokemon_species"."identifier" AS "species_identifier",
       "pokemon_forms"."id"           AS "form_id",
       "pokemon_forms"."form_identifier",
       "version_groups"."order"       AS "version_group_order"
FROM "pokemon_forms"
       JOIN "version_groups"
       JOIN "pokemon" ON "pokemon_forms"."pokemon_id" = "pokemon"."id"
       JOIN "pokemon_species" ON "pokemon"."species_id" = "pokemon_species"."id"
WHERE "version_groups"."order" >= "pokemon_forms"."introduced_in_version_group_id"
  AND "version_groups"."identifier" = 'yellow'
UNION
SELECT "version_groups"."identifier"  AS "version_group",
       'shiny'                        AS "group",
       "pokemon_species"."id"         AS "species_id",
       "pokemon_species"."identifier" AS "species_identifier",
       "pokemon_forms"."id"           AS "form_id",
       "pokemon_forms"."form_identifier",
       "version_groups"."order"       AS "version_group_order"
FROM "pokemon_forms"
       JOIN "version_groups"
       JOIN "pokemon" ON "pokemon_forms"."pokemon_id" = "pokemon"."id"
       JOIN "pokemon_species" ON "pokemon"."species_id" = "pokemon_species"."id"
WHERE "version_groups"."order" >= "pokemon_forms"."introduced_in_version_group_id"
  AND "version_groups"."generation_id" >= 2
UNION
SELECT "version_groups"."identifier"  AS "version_group",
       'shiny/female'                 AS "group",
       "pokemon_species"."id"         AS "species_id",
       "pokemon_species"."identifier" AS "species_identifier",
       "pokemon_forms"."id"           AS "form_id",
       "pokemon_forms"."form_identifier",
       "version_groups"."order"       AS "version_group_order"
FROM "pokemon_forms"
       JOIN "version_groups"
       JOIN "pokemon" ON "pokemon_forms"."pokemon_id" = "pokemon"."id"
       JOIN "pokemon_species" ON "pokemon"."species_id" = "pokemon_species"."id"
WHERE "version_groups"."order" >= "pokemon_forms"."introduced_in_version_group_id"
  AND "version_groups"."generation_id" >= 4
UNION
SELECT "version_groups"."identifier"  AS "version_group",
       'animated'                     AS "group",
       "pokemon_species"."id"         AS "species_id",
       "pokemon_species"."identifier" AS "species_identifier",
       "pokemon_forms"."id"           AS "form_id",
       "pokemon_forms"."form_identifier",
       "version_groups"."order"       AS "version_group_order"
FROM "pokemon_forms"
       JOIN "version_groups"
       JOIN "pokemon" ON "pokemon_forms"."pokemon_id" = "pokemon"."id"
       JOIN "pokemon_species" ON "pokemon"."species_id" = "pokemon_species"."id"
WHERE "version_groups"."order" >= "pokemon_forms"."introduced_in_version_group_id"
  AND "version_groups"."identifier" IN ('crystal', 'emerald')
UNION
SELECT "version_groups"."identifier"  AS "version_group",
       'animated/shiny'               AS "group",
       "pokemon_species"."id"         AS "species_id",
       "pokemon_species"."identifier" AS "species_identifier",
       "pokemon_forms"."id"           AS "form_id",
       "pokemon_forms"."form_identifier",
       "version_groups"."order"       AS "version_group_order"
FROM "pokemon_forms"
       JOIN "version_groups"
       JOIN "pokemon" ON "pokemon_forms"."pokemon_id" = "pokemon"."id"
       JOIN "pokemon_species" ON "pokemon"."species_id" = "pokemon_species"."id"
WHERE "version_groups"."order" >= "pokemon_forms"."introduced_in_version_group_id"
  AND "version_groups"."identifier" IN ('crystal', 'emerald')
UNION
SELECT "version_groups"."identifier"  AS "version_group",
       'female'                       AS "group",
       "pokemon_species"."id"         AS "species_id",
       "pokemon_species"."identifier" AS "species_identifier",
       "pokemon_forms"."id"           AS "form_id",
       "pokemon_forms"."form_identifier",
       "version_groups"."order"       AS "version_group_order"
FROM "pokemon_forms"
       JOIN "version_groups"
       JOIN "pokemon" ON "pokemon_forms"."pokemon_id" = "pokemon"."id"
       JOIN "pokemon_species" ON "pokemon"."species_id" = "pokemon_species"."id"
WHERE "version_groups"."order" >= "pokemon_forms"."introduced_in_version_group_id"
  AND "version_groups"."generation_id" >= 4
ORDER BY "version_group_order", "group", "species_id", "form_id";
SQL
        );

        $sourceDriver->setCountStatement(
            <<<SQL
SELECT "default_count"."count"
         + "back_count"."count"
         + "back_female_count"."count"
         + "back_shiny_count"."count"
         + "gray_count"."count"
         + "gbc_count"."count"
         + "shiny_count"."count"
         + "shiny_female_count"."count"
         + "animated_count"."count"
         + "animated_shiny_count"."count"
         + "female_count"."count"
FROM (
       SELECT count(*) AS "count"
       FROM "pokemon_forms"
              JOIN "version_groups"
       WHERE "version_groups"."order" >= "pokemon_forms"."introduced_in_version_group_id"
     ) "default_count",
     (
       SELECT count(*) AS "count"
       FROM "pokemon_forms"
              JOIN "version_groups"
       WHERE "version_groups"."order" >= "pokemon_forms"."introduced_in_version_group_id"
         -- Gen 6 eliminated back sprites with the switch to 3D
         AND "version_groups"."generation_id" < 6
     ) "back_count",
     (
       SELECT count(*) AS "count"
       FROM "pokemon_forms"
              JOIN "version_groups"
       WHERE "version_groups"."order" >= "pokemon_forms"."introduced_in_version_group_id"
         AND "version_groups"."generation_id" < 6
         AND "version_groups"."generation_id" >= 4
     ) "back_female_count",
     (
       SELECT count(*) AS "count"
       FROM "pokemon_forms"
              JOIN "version_groups"
       WHERE "version_groups"."order" >= "pokemon_forms"."introduced_in_version_group_id"
         AND "version_groups"."generation_id" < 6
         AND "version_groups"."generation_id" >= 4
     ) "back_shiny_count",
     (
       SELECT count(*) AS "count"
       FROM "pokemon_forms"
              JOIN "version_groups"
       WHERE "version_groups"."order" >= "pokemon_forms"."introduced_in_version_group_id"
         AND "version_groups"."generation_id" = 1
     ) "gray_count",
     (
       SELECT count(*) AS "count"
       FROM "pokemon_forms"
              JOIN "version_groups"
       WHERE "version_groups"."order" >= "pokemon_forms"."introduced_in_version_group_id"
         AND "version_groups"."identifier" = 'yellow'
     ) "gbc_count",
     (
       SELECT count(*) AS "count"
       FROM "pokemon_forms"
              JOIN "version_groups"
       WHERE "version_groups"."order" >= "pokemon_forms"."introduced_in_version_group_id"
         AND "version_groups"."generation_id" >= 2
     ) "shiny_count",
     (
       SELECT count(*) AS "count"
       FROM "pokemon_forms"
              JOIN "version_groups"
       WHERE "version_groups"."order" >= "pokemon_forms"."introduced_in_version_group_id"
         AND "version_groups"."generation_id" >= 4
     ) "shiny_female_count",
     (
       SELECT count(*) AS "count"
       FROM "pokemon_forms"
              JOIN "version_groups"
       WHERE "version_groups"."order" >= "pokemon_forms"."introduced_in_version_group_id"
         AND "version_groups"."identifier" IN ('crystal', 'emerald')
     ) "animated_count",
     (
       SELECT count(*) AS "count"
       FROM "pokemon_forms"
              JOIN "version_groups"
       WHERE "version_groups"."order" >= "pokemon_forms"."introduced_in_version_group_id"
         AND "version_groups"."identifier" IN ('crystal', 'emerald')
     ) "animated_shiny_count",
     (
       SELECT count(*) AS "count"
       FROM "pokemon_forms"
              JOIN "version_groups"
       WHERE "version_groups"."order" >= "pokemon_forms"."introduced_in_version_group_id"
         AND "version_groups"."generation_id" >= 4
     ) "female_count";
SQL
        );
    }

    /**
     * {@inheritdoc}
     */
    public function transform($sourceData, $destinationData)
    {
        if (strpos($sourceData['group'], 'animated') !== false) {
            $ext = 'gif';
            $convertGif = true;
        } else {
            $ext = 'png';
            $convertGif = false;
        }
        $oldFilename = $this->buildFilename($sourceData['species_id'], $sourceData['form_identifier'], $ext);
        $newFilename = sprintf(
            '%s-%s.%s',
            $sourceData['species_identifier'],
            $sourceData['form_identifier'] ?? 'default',
            $ext === 'gif' ? 'webm' : $ext
        );
        if (!empty($sourceData['group'])) {
            $oldFilename = $sourceData['group'].'/'.$oldFilename;
            $newFilename = $sourceData['group'].'/'.$newFilename;
        }
        $oldPath = sprintf(
            '%s/pokedex-media/pokemon/main-sprites/%s/%s',
            $this->resourcesDir,
            $sourceData['version_group'] === 'gold-silver' ? 'gold' : $sourceData['version_group'],
            $oldFilename
        );
        $newPath = sprintf(
            '%s/%s/%s',
            $this->destinationPath,
            $sourceData['version_group'],
            $newFilename
        );

        if (!is_file($newPath)) {
            if (is_file($oldPath)) {
                if (!is_dir(dirname($newPath))) {
                    mkdir(dirname($newPath), 0755, true);
                }
                if ($convertGif) {
                    $cmd = [
                        'ffmpeg',
                        '-i',
                        $oldPath,
                        '-c:v',
                        'libvpx',
                        '-qmin',
                        '0',
                        '-qmax',
                        '18',
                        '-crf',
                        '9',
                        '-b:v',
                        '1400K',
                        '-quality',
                        'good',
                        '-cpu-used',
                        '0',
                        '-auto-alt-ref',
                        '0',
                        '-pix_fmt',
                        'yuva420p',
                        '-an',
                        '-sn',
                        $newPath,
                    ];
                    $process = new Process($cmd);
                    $process->mustRun();
                } else {
                    copy($oldPath, $newPath);
                }
            } else {
                // Path doesn't exist
                $missingLogPath = $this->destinationPath.'/missing.csv';
                if (!is_file($missingLogPath)) {
                    $handle = fopen($missingLogPath, 'wb');
                    fputcsv(
                        $handle,
                        ['version_group', 'group', 'species_id', 'species_identifier', 'form_identifier']
                    );
                } else {
                    $handle = fopen($missingLogPath, 'ab');
                }
                fputcsv(
                    $handle,
                    [
                        $sourceData['version_group'],
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
            'version_group' => $sourceData['version_group'],
            'id' => $newFilename,
        ];
    }
}