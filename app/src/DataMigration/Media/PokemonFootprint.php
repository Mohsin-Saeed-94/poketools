<?php

namespace App\DataMigration\Media;


use DragoonBoots\A2B\Annotations\DataMigration;
use DragoonBoots\A2B\Annotations\IdField;
use DragoonBoots\A2B\Drivers\Source\DbalSourceDriver;
use DragoonBoots\A2B\Drivers\SourceDriverInterface;

/**
 * Pokemon footprint migration.
 *
 * @DataMigration(
 *     name="Pokemon Icon",
 *     group="Media",
 *     source="veekun",
 *     sourceIds={@IdField(name="id")},
 *     destination="file:///%kernel.project_dir%/assets/static/pokemon/footprint",
 *     destinationIds={@IdField(name="id", type="string")}
 * )
 */
class PokemonFootprint extends AbstractMediaMigration
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
SELECT "pokemon_species"."id",
       "pokemon_species"."identifier"
FROM "pokemon_species"
WHERE "pokemon_species"."generation_id" < 6;
SQL
        );

        $sourceDriver->setCountStatement(
            <<<SQL
SELECT count(*)
FROM "pokemon_species"
WHERE "pokemon_species"."generation_id" < 6;
SQL
        );
    }

    /**
     * {@inheritdoc}
     */
    public function transform($sourceData, $destinationData)
    {
        $oldFilename = $sourceData['id'].'.png';
        $newFilename = $sourceData['identifier'].'.png';
        $oldPath = $this->resourcesDir.'/pokedex-media/pokemon/footprints/'.$oldFilename;
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
                    fputcsv($handle, ['species_id', 'species_identifier']);
                } else {
                    $handle = fopen($missingLogPath, 'ab');
                }
                fputcsv(
                    $handle,
                    [
                        $sourceData['id'],
                        $sourceData['identifier'],
                    ]
                );
                fclose($handle);

                return null;
            }
        }

        return [
            'id' => $newFilename,
        ];
    }
}