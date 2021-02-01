<?php

namespace App\DataMigration;


use App\DataMigration\Helpers\PokemonLookup;
use App\Entity\ItemInVersionGroup;
use App\Entity\MoveInVersionGroup;
use DragoonBoots\A2B\Annotations\DataMigration;
use DragoonBoots\A2B\Annotations\IdField;
use DragoonBoots\A2B\DataMigration\DataMigrationInterface;
use DragoonBoots\A2B\DataMigration\MigrationReferenceStoreInterface;
use Ds\Map;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Pokemon Move migration.
 *
 * @DataMigration(
 *     name="Pokemon Move",
 *     source="/%kernel.project_dir%/resources/data/pokemon_move.csv",
 *     sourceDriver="DragoonBoots\A2B\Drivers\Source\CsvSourceDriver",
 *     sourceIds={
 *         @IdField(name="pokemon", type="string"),
 *         @IdField(name="version_group", type="string"),
 *         @IdField(name="move", type="string"),
 *         @IdField(name="learn_method", type="string")
 *     },
 *     destination="pokemon_move",
 *     destinationIds={
 *         @IdField(name="id")
 *     },
 *     destinationDriver="App\A2B\Drivers\Destination\DbalDestinationDriver",
 *     depends={
 *         "App\DataMigration\PokemonSpecies",
 *         "App\DataMigration\Move",
 *         "App\DataMigration\MoveLearnMethod",
 *         "App\DataMigration\VersionGroup",
 *         "App\DataMigration\Item"
 *     }
 * )
 */
class PokemonMove extends AbstractDoctrineDataMigration implements DataMigrationInterface
{
    private PokemonLookup $pokemonLookupHelper;

    private Map $moves;
    private Map $versionGroupMoves;
    private Map $items;
    private Map $versionGroupItems;

    /**
     * @inheritDoc
     */
    public function __construct(
        MigrationReferenceStoreInterface $referenceStore,
        PropertyAccessorInterface $propertyAccess
    ) {
        parent::__construct($referenceStore, $propertyAccess);
        $this->pokemonLookupHelper = new PokemonLookup($referenceStore);
        $this->moves = new Map();
        $this->versionGroupMoves = new Map();
        $this->items = new Map();
        $this->versionGroupItems = new Map();
    }

    /**
     * {@inheritdoc}
     */
    public function transform($sourceData, $destinationData)
    {
        static $pokemonMoveId = 1;
        $sourceData['id'] = $pokemonMoveId;
        $pokemonMoveId++;

        static $position = 1;
        $sourceData['position'] = $position;
        $position++;

        // Find the correct pokemon
        $versionGroup = $this->referenceStore->get(VersionGroup::class, ['identifier' => $sourceData['version_group']]);
        $pokemon = $this->pokemonLookupHelper->lookupPokemon(
            $versionGroup,
            $sourceData['species'],
            $sourceData['pokemon']
        );
        $sourceData['pokemon_id'] = $pokemon->getId();
        unset($sourceData['version_group'], $sourceData['species'], $sourceData['pokemon']);;
        $sourceData['move_id'] = $this->lookupMove($versionGroup, $sourceData['move'])->getId();
        unset($sourceData['move']);

        // Remove nulls and blank strings
        $sourceData = array_filter(
            $sourceData,
            function ($value) {
                return (!is_null($value)) && ($value !== '');
            }
        );

        $sourceData['learn_method'] = $this->referenceStore->get(
            MoveLearnMethod::class,
            ['identifier' => $sourceData['learn_method']]
        );
        $sourceData['learn_method_id'] = $sourceData['learn_method']->getId();
        unset($sourceData['learn_method']);

        if (isset($sourceData['machine'])) {
            $item = $this->lookupItem($versionGroup, $sourceData['machine']);

            // @TODO This is a failsafe if the item does not exist in the dataset yet.
            if (!is_null($item)) {
                $sourceData['machine_id'] = $item->getId();
            }
            unset($sourceData['machine']);
        } else {
            $sourceData['machine_id'] = null;
        }
        if (isset($sourceData['level'])) {
            $sourceData['level'] = (int)$sourceData['level'];
        } else {
            $sourceData['level'] = null;
        }

        $destinationData = array_merge($destinationData, $sourceData);

        return $destinationData;
    }

    /**
     * @inheritDoc
     */
    public function cleanup(): void
    {
        unset($this->pokemonLookupHelper);
        $this->moves->clear();
        $this->versionGroupMoves->clear();
        $this->items->clear();
        $this->versionGroupItems->clear();
    }

    /**
     * Lookup move from the cache or fetch it from the database
     *
     * @param \App\Entity\VersionGroup $versionGroup
     * @param string $identifier
     *
     * @return MoveInVersionGroup|null
     * @throws \DragoonBoots\A2B\Exception\NoMappingForIdsException
     */
    private function lookupMove(\App\Entity\VersionGroup $versionGroup, string $identifier): ?MoveInVersionGroup
    {
        if (!$this->versionGroupMoves->hasKey($versionGroup->getId())) {
            $this->versionGroupMoves->put($versionGroup->getId(), new Map());
        }
        /** @var Map $versionGroupMoves */
        $versionGroupMoves = $this->versionGroupMoves->get($versionGroup->getId());
        if (!$versionGroupMoves->hasKey($identifier)) {
            if (!$this->moves->hasKey($identifier)) {
                // Fetch entity
                $this->moves->put($identifier, $this->referenceStore->get(Move::class, ['identifier' => $identifier]));
            }
            /** @var \App\Entity\Move $move */
            $move = $this->moves->get($identifier);
            $versionGroupMoves->put($identifier, $move->findChildByGrouping($versionGroup));
        }

        return $versionGroupMoves->get($identifier);
    }

    /**
     * Lookup move from the cache or fetch it from the database
     *
     * @param \App\Entity\VersionGroup $versionGroup
     * @param string $identifier
     *
     * @return ItemInVersionGroup|null
     * @throws \DragoonBoots\A2B\Exception\NoMappingForIdsException
     */
    private function lookupItem(\App\Entity\VersionGroup $versionGroup, string $identifier): ?ItemInVersionGroup
    {
        if (!$this->versionGroupItems->hasKey($versionGroup->getId())) {
            $this->versionGroupItems->put($versionGroup->getId(), new Map());
        }
        /** @var Map $versionGroupItems */
        $versionGroupItems = $this->versionGroupItems->get($versionGroup->getId());
        if (!$versionGroupItems->hasKey($identifier)) {
            if (!$this->items->hasKey($identifier)) {
                // Fetch entity
                $this->items->put($identifier, $this->referenceStore->get(Item::class, ['identifier' => $identifier]));
            }
            /** @var \App\Entity\Item $item */
            $item = $this->items->get($identifier);
            $versionGroupItems->put($identifier, $item->findChildByGrouping($versionGroup));
        }

        return $versionGroupItems->get($identifier);
    }

}
