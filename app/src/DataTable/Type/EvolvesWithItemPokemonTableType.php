<?php

namespace App\DataTable\Type;


use App\DataTable\Column\LinkColumn;
use App\Entity\ItemInVersionGroup;
use App\Entity\Pokemon;
use App\Entity\PokemonEvolutionCondition\HeldItemEvolutionCondition;
use App\Entity\PokemonEvolutionCondition\TriggerItemEvolutionCondition;
use App\Entity\Version;
use Doctrine\ORM\QueryBuilder;
use Omines\DataTablesBundle\Adapter\Doctrine\ORMAdapter;
use Omines\DataTablesBundle\DataTable;

/**
 * Pokemon Table to show those that evolves with a specific item
 */
class EvolvesWithItemPokemonTableType extends PokemonTableType
{

    /**
     * {@inheritdoc}
     */
    public function configure(DataTable $dataTable, array $options)
    {
        /** @var Version $version */
        $version = $options['version'];
        /** @var ItemInVersionGroup $item */
        $item = $options['item'];

        $dataTable->add(
            'evolutionParent',
            LinkColumn::class,
            [
                'label' => 'Evolves from',
                'propertyPath' => 'evolutionParent',
                'route' => 'pokemon_view',
                'routeParams' => [
                    'speciesSlug' => function (Pokemon $pokemon) {
                        return $pokemon->getEvolutionParent()->getSpecies()->getSlug();
                    },
                    'pokemonSlug' => function (Pokemon $pokemon) {
                        if (!$pokemon->getEvolutionParent()->isDefault()) {
                            return $pokemon->getEvolutionParent()->getSlug();
                        }

                        return null;
                    },
                    'versionSlug' => $version->getSlug(),
                ],
                'render' => function ($name, $pokemon) use ($version) {
                    // If this table is extended, the context can be something other than a Pokemon.
                    /** @var Pokemon $pokemon */
                    if (!is_a($pokemon, Pokemon::class)) {
                        if (method_exists($pokemon, 'getPokemon')) {
                            $pokemon = $pokemon->getPokemon();
                        } else {
                            return null;
                        }
                    }

                    return $this->labeler->pokemon($pokemon->getEvolutionParent(), $version);
                },
            ]
        );

        parent::configure($dataTable, $options);

        $dataTable->setName(self::class)->createAdapter(
            ORMAdapter::class,
            [
                'entity' => Pokemon::class,
                'query' => function (QueryBuilder $qb) use ($version, $item) {
                    $triggerItemQb = new QueryBuilder($qb->getEntityManager());
                    $triggerItemQb->from(TriggerItemEvolutionCondition::class, 'trigger_item_evolution_condition')
                        ->select('trigger_item_evolution_condition.id')
                        ->join('trigger_item_evolution_condition.evolutionTrigger', 'trigger_item_evolution_trigger')
                        ->where("trigger_item_evolution_trigger.slug = 'use-item'")
                        ->andWhere('trigger_item_evolution_condition.triggerItem = :item');

                    $heldItemQb = new QueryBuilder($qb->getEntityManager());
                    $heldItemQb->from(HeldItemEvolutionCondition::class, 'held_item_evolution_condition')
                        ->select('held_item_evolution_condition.id')
                        ->join('held_item_evolution_condition.evolutionTrigger', 'held_item_evolution_trigger')
//                        ->where("held_item_evolution_trigger.slug = 'trade'")
                        ->andWhere('held_item_evolution_condition.heldItem = :item');

                    $this->query($qb, $version);
                    $qb->join('pokemon.evolutionConditions', 'evolution_conditions')
                        ->andWhere(
                            $qb->expr()->orX(
                                $qb->expr()->in('evolution_conditions.id', $triggerItemQb->getDQL()),
                                $qb->expr()->in('evolution_conditions.id', $heldItemQb->getDQL())
                            )
                        )
                        ->andWhere('pokemon.mega = false')
                        ->setParameter('item', $item);
                },
            ]
        );
    }

}
