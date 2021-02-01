<?php

namespace App\DataMigration;


use App\DataMigration\Helpers\PokemonLookup;
use DragoonBoots\A2B\Annotations\DataMigration;
use DragoonBoots\A2B\Annotations\IdField;
use DragoonBoots\A2B\DataMigration\DataMigrationInterface;
use DragoonBoots\A2B\DataMigration\MigrationReferenceStoreInterface;
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

    /**
     * @inheritDoc
     */
    public function __construct(
        MigrationReferenceStoreInterface $referenceStore,
        PropertyAccessorInterface $propertyAccess
    ) {
        parent::__construct($referenceStore, $propertyAccess);
        $this->pokemonLookupHelper = new PokemonLookup($referenceStore);
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
        unset($sourceData['version_group'], $sourceData['species'], $sourceData['pokemon']);

        /** @var \App\Entity\Move $move */
        $move = $this->referenceStore->get(Move::class, ['identifier' => $sourceData['move']]);
        $move = $move->findChildByGrouping($versionGroup);
        $sourceData['move_id'] = $move->getId();
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
            /** @var \App\Entity\Item $item */
            $item = $this->referenceStore->get(Item::class, ['identifier' => $sourceData['machine']]);
            $item = $item->findChildByGrouping($versionGroup);

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
}
