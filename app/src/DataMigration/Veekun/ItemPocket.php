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
 * Item Pocket migration.
 *
 * @DataMigration(
 *     name="Item Pocket",
 *     group="Veekun",
 *     source="veekun",
 *     sourceIds={@IdField(name="id")},
 *     destination="/%kernel.project_dir%/resources/data/item_pocket",
 *     destinationDriver="DragoonBoots\A2B\Drivers\Destination\YamlDestinationDriver",
 *     destinationIds={@IdField(name="identifier", type="string")}
 * )
 */
class ItemPocket extends AbstractDataMigration implements DataMigrationInterface
{

    const BLACKLIST_ICON = [
        'colosseum',
        'xd',
    ];

    protected $versionGroups = [];

    /**
     * {@inheritdoc}
     * @param DbalSourceDriver $sourceDriver
     */
    public function configureSource(SourceDriverInterface $sourceDriver)
    {
        $sourceDriver->setStatement(
            <<<SQL
SELECT "item_pockets"."id",
       "item_pockets"."identifier",
       "item_pocket_names"."name"
FROM "item_pockets"
     JOIN "item_pocket_names"
         ON "item_pockets"."id" = "item_pocket_names"."item_pocket_id"
WHERE "item_pocket_names"."local_language_id" = 9;
SQL
        );

        $sourceDriver->setCountStatement(
            <<<SQL
SELECT count(*)
FROM "item_pockets";
SQL
        );

        // Store a list of version groups with special bags
        $this->versionGroups = $sourceDriver->getConnection()->query(
            <<<SQL
SELECT "identifier"
FROM "version_groups"
WHERE "generation_id" >= 2
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
        // Veekun is VERY incomplete for item pockets, so assume that any data
        // that already exists is complete.
        if (!empty($destinationData)) {
            return $destinationData;
        }

        unset($sourceData['id']);
        $sourceData['position'] = 0;
        $identifier = $sourceData['identifier'];
        $destinationData['identifier'] = $identifier;
        unset($sourceData['identifier']);

        foreach ($this->versionGroups as $versionGroup) {
            if (!in_array($versionGroup, self::BLACKLIST_ICON)) {
                $sourceData['icon'] = sprintf('%s/%s.png', $versionGroup, $identifier);
            } else {
                $sourceData['icon'] = null;
            }
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
