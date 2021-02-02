<?php


namespace App\Search;

use App\Entity\AbilityInVersionGroup;
use App\Entity\ItemInVersionGroup;
use App\Entity\LocationInVersionGroup;
use App\Entity\MoveInVersionGroup;
use App\Entity\Nature;
use App\Entity\Pokemon;
use App\Entity\Type;
use TeamTNT\TNTSearch\TNTSearch;

/**
 * Create and update search index
 */
class Indexer
{
    public const IX_NAME = 'name';

    private TNTSearch $tnt;

    /**
     * Indexer constructor.
     *
     * @param TNTSearch $tnt
     */
    public function __construct(TNTSearch $tnt)
    {
        $this->tnt = $tnt;
    }

    public function update(bool $output = true)
    {
        // Remove existing indexes, if applicable
        $existing = glob($this->tnt->config['storage'].'[!.]*');
        foreach ($existing as $indexStorage) {
            unlink($indexStorage);
        }

        /**
         * Each callable must take a single bool parameter that enables output.
         *
         * @var callable[] $indexers
         */
        $indexers = [
            self::IX_NAME => [$this, 'indexName'],
        ];
        foreach ($indexers as $index => $indexer) {
            if ($output) {
                echo sprintf("Indexing %s... \n", $index);
            }
            $indexer($output);
        }
    }

    private function indexName(bool $output): void
    {
        $index = $this->tnt->createIndex(self::IX_NAME, !$output);
        $classes = [
            'pokemon' => Pokemon::class,
            'move' => MoveInVersionGroup::class,
            'type' => Type::class,
            'item' => ItemInVersionGroup::class,
            'location' => LocationInVersionGroup::class,
            'nature' => Nature::class,
            'ability' => AbilityInVersionGroup::class,
        ];
        $index->query(
            <<<SQL
            SELECT '${classes['pokemon']}__' || "id" AS "id", "name"
            FROM "pokemon"
            UNION
            SELECT '${classes['move']}__' || "id" AS "id", "name"
            FROM "move_in_version_group"
            UNION
            SELECT '${classes['type']}__' || "id" AS "id", "name"
            FROM "type"
            UNION
            SELECT '${classes['item']}__' || "id" AS "id", "name"
            FROM "item_in_version_group"
            UNION 
            SELECT '${classes['location']}__' || "id" AS "id", "name"
            FROM "location_in_version_group"
            UNION
            SELECT '${classes['nature']}__' || "id" AS "id", "name"
            FROM "nature"
            UNION
            SELECT '${classes['ability']}__' || "id" AS "id", "name"
            FROM "ability_in_version_group";
            SQL
        );
        $index->run();
    }
}
