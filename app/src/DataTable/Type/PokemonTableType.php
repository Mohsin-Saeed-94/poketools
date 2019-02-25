<?php
/**
 * @file AbilityTableType.php
 */

namespace App\DataTable\Type;


use App\DataTable\Adapter\ObjectAdapter;
use App\DataTable\Column\CollectionColumn;
use App\DataTable\Column\LinkColumn;
use App\Entity\PokemonAbility;
use App\Entity\PokemonStat;
use App\Entity\PokemonType;
use App\Entity\Version;
use Doctrine\Common\Collections\Collection;
use Omines\DataTablesBundle\Column\TextColumn;
use Omines\DataTablesBundle\DataTable;
use Omines\DataTablesBundle\DataTableTypeInterface;

/**
 * Pokemon table
 */
abstract class PokemonTableType implements DataTableTypeInterface
{
    /**
     * @param DataTable $dataTable
     * @param array $options
     */
    public function configure(DataTable $dataTable, array $options)
    {
        /** @var Version $version */
        $version = $options['version'];

        $dataTable->createAdapter(ObjectAdapter::class)
            ->add(
                'name',
                LinkColumn::class,
                [
                    'label' => 'Name',
                    // @todo Pokemon link
                    'uri' => '#',
                    'orderable' => true,
                    'className' => 'pkt-pokemon-index-table-name',
                ]
            )->add(
                'types',
                CollectionColumn::class,
                [
                    'label' => 'Type',
                    'orderable' => true,
                    'className' => 'pkt-pokemon-index-table-type',
                    'childType' => LinkColumn::class,
                    'childOptions' => [
                        // @todo Type link
                        'uri' => '#',
                        'linkClassName' => function (PokemonType $pokemonType): ?string {
                            return sprintf('pkt-type-emblem-%s', $pokemonType->getType()->getSlug());
                        },
                    ],
                ]
            )->add(
                'abilities',
                CollectionColumn::class,
                [
                    'label' => 'Abilities',
                    'orderable' => true,
                    'className' => 'pkt-pokemon-index-table-ability',
                    'childType' => LinkColumn::class,
                    'childOptions' => [
                        'route' => 'ability_view',
                        'routeParams' => [
                            'versionSlug' => $version->getSlug(),
                            'abilitySlug' => function (PokemonAbility $context, $value) {
                                return $context->getAbility()->getSlug();
                            },
                        ],
                        'linkClassName' => function (PokemonAbility $pokemonAbility): ?string {
                            if ($pokemonAbility->isHidden()) {
                                return 'pkt-pokemon-index-ability-hidden';
                            }

                            return null;
                        },
                    ],
                ]
            )->add(
                'hp',
                TextColumn::class,
                [
                    'label' => 'HP',
                    'propertyPath' => 'stats',
                    'orderable' => true,
                    'className' => 'pkt-pokemon-index-table-hp',
                    'data' => function ($context, $value) {
                        return $this->renderPokemonStat($this->filterStatsCollection('hp', $value));
                    },
                ]
            )->add(
                'attack',
                TextColumn::class,
                [
                    'label' => 'Atk.',
                    'propertyPath' => 'stats',
                    'orderable' => true,
                    'className' => 'pkt-pokemon-index-table-attack',
                    'data' => function ($context, $value) {
                        return $this->renderPokemonStat($this->filterStatsCollection('attack', $value));
                    },
                ]
            )->add(
                'defense',
                TextColumn::class,
                [
                    'label' => 'Def.',
                    'propertyPath' => 'stats',
                    'orderable' => true,
                    'className' => 'pkt-pokemon-index-table-defense',
                    'data' => function ($context, $value) {
                        return $this->renderPokemonStat($this->filterStatsCollection('defense', $value));
                    },
                ]
            )->add(
                'special-attack',
                TextColumn::class,
                [
                    'label' => 'Sp. Atk.',
                    'propertyPath' => 'stats',
                    'orderable' => true,
                    'className' => 'pkt-pokemon-index-table-specialattack',
                    'data' => function ($context, $value) {
                        return $this->renderPokemonStat($this->filterStatsCollection('special-attack', $value));
                    },
                ]
            )->add(
                'special-defense',
                TextColumn::class,
                [
                    'label' => 'Sp. Def.',
                    'propertyPath' => 'stats',
                    'orderable' => true,
                    'className' => 'pkt-pokemon-index-table-specialdefense',
                    'data' => function ($context, $value) {
                        return $this->renderPokemonStat($this->filterStatsCollection('special-defense', $value));
                    },
                ]
            )->add(
                'speed',
                TextColumn::class,
                [
                    'label' => 'Spd',
                    'propertyPath' => 'stats',
                    'orderable' => true,
                    'className' => 'pkt-pokemon-index-table-speed',
                    'data' => function ($context, $value) {
                        return $this->renderPokemonStat($this->filterStatsCollection('speed', $value));
                    },
                ]
            )->add(
                'stat_total',
                TextColumn::class,
                [
                    'label' => 'Total',
                    'orderable' => true,
                    'className' => 'pkt-pokemon-index-table-total',
                ]
            )->addOrderBy('name');
    }

    /**
     * @param PokemonStat $pokemonStat
     *
     * @return int|null
     */
    private function renderPokemonStat(?PokemonStat $pokemonStat): ?int
    {
        if ($pokemonStat !== null) {
            return $pokemonStat->getBaseValue();
        }

        return null;
    }

    /**
     * @param string $stat
     * @param PokemonStat[]|Collection $collection
     *
     * @return PokemonStat|null
     */
    private function filterStatsCollection(string $stat, Collection $collection): ?PokemonStat
    {
        foreach ($collection as $item) {
            if ($item->getStat()->getSlug() === $stat) {
                return $item;
            }
        }

        return null;
    }
}
