<?php

namespace App\DataMigration\Veekun;

use Doctrine\DBAL\FetchMode;
use DragoonBoots\A2B\Annotations\DataMigration;
use DragoonBoots\A2B\Annotations\IdField;
use DragoonBoots\A2B\DataMigration\AbstractDataMigration;
use DragoonBoots\A2B\DataMigration\DataMigrationInterface;
use DragoonBoots\A2B\Drivers\Destination\YamlDestinationDriver;
use DragoonBoots\A2B\Drivers\DestinationDriverInterface;
use DragoonBoots\A2B\Drivers\Source\DbalSourceDriver;
use DragoonBoots\A2B\Drivers\SourceDriverInterface;

/**
 * Pokemon Shape migration.
 *
 * @DataMigration(
 *     name="Pokemon Shape",
 *     group="Veekun",
 *     source="veekun",
 *     sourceIds={@IdField(name="id")},
 *     destination="yaml:///%kernel.project_dir%/resources/data/pokemon_shape",
 *     destinationIds={@IdField(name="identifier", type="string")}
 * )
 */
class PokemonShape extends AbstractDataMigration implements DataMigrationInterface
{

    protected $versionGroups = [];

    /**
     * {@inheritdoc}
     * @param DbalSourceDriver $sourceDriver
     */
    public function configureSource(SourceDriverInterface $sourceDriver)
    {
        $sourceDriver->setStatement(
            <<<SQL
SELECT "pokemon_shapes"."id",
       "pokemon_shapes"."identifier",
       "pokemon_shape_prose"."name",
       "pokemon_shape_prose"."awesome_name" AS "taxonomy_name",
       "pokemon_shape_prose"."description"
FROM "pokemon_shapes"
     JOIN "pokemon_shape_prose"
         ON "pokemon_shapes"."id" = "pokemon_shape_prose"."pokemon_shape_id"
WHERE "pokemon_shape_prose"."local_language_id" = 9;
SQL
        );

        $sourceDriver->setCountStatement(
            <<<SQL
SELECT count(*)
FROM "pokemon_shapes";
SQL
        );

        // Store a list of version groups that have shapes.
        $this->versionGroups = $sourceDriver->getConnection()->query(
            <<<SQL
SELECT "identifier"
FROM "version_groups"
WHERE "generation_id" >= 4
ORDER BY "order";
SQL
        )->fetchAll(FetchMode::NUMERIC);
        $this->versionGroups = array_column($this->versionGroups, 0);
    }

    /**
     * {@inheritdoc}
     */
    public function transform($sourceData, $destinationData)
    {
        unset($sourceData['id']);
        $sourceData['icon'] = sprintf('%s.png', $sourceData['identifier']);
        $destinationData['identifier'] = $sourceData['identifier'];
        unset($sourceData['identifier']);
        foreach ($this->versionGroups as $versionGroup) {
            $destinationData[$versionGroup] = array_merge($sourceData, $destinationData[$versionGroup] ?? []);
        }

        return $destinationData;
    }

    /**
     * {@inheritdoc}
     * @param YamlDestinationDriver $destinationDriver
     */
    public function configureDestination(DestinationDriverInterface $destinationDriver)
    {
        $destinationDriver->setOption('refs', true);
    }
}
