<?php

namespace App\DataTable\Type;


use App\DataTable\Column\CollectionColumn;
use App\DataTable\Column\LinkColumn;
use App\Entity\Pokemon;
use App\Entity\PokemonAbility;
use App\Entity\PokemonStat;
use App\Entity\Version;
use App\Helpers\Labeler;
use App\Repository\PokemonRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\QueryBuilder;
use Omines\DataTablesBundle\Adapter\Doctrine\ORMAdapter;
use Omines\DataTablesBundle\Column\TextColumn;
use Omines\DataTablesBundle\DataTable;
use Omines\DataTablesBundle\DataTableTypeInterface;

/**
 * Pokemon table
 */
class PokemonTableType implements DataTableTypeInterface
{
    /**
     * @var PokemonRepository
     */
    protected $pokemonRepo;
    /**
     * @var Labeler
     */
    protected $labeler;

    /**
     * PokemonTableType constructor.
     *
     * @param Labeler $labeler
     * @param PokemonRepository $pokemonRepo
     */
    public function __construct(Labeler $labeler, PokemonRepository $pokemonRepo)
    {
        $this->labeler = $labeler;
        $this->pokemonRepo = $pokemonRepo;
    }

    /**
     * @param DataTable $dataTable
     * @param array $options
     */
    public function configure(DataTable $dataTable, array $options)
    {
        /** @var Version $version */
        $version = $options['version'];

        $dataTable->setName(self::class)->add(
            'name',
            LinkColumn::class,
            [
                'label' => 'Name',
                // @todo Pokemon link
                'uri' => '#',
                'orderable' => false,
//                'orderField' => 'pokemon.name',
                'className' => 'pkt-pokemon-index-table-name',
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

                    return $this->labeler->pokemon($pokemon, $version);
                },
            ]
        )->add(
            'types',
            CollectionColumn::class,
            [
                'label' => 'Type',
                'propertyPath' => 'types',
//                'orderable' => true,
                'className' => 'pkt-pokemon-index-table-type',
                'childType' => LinkColumn::class,
                'childOptions' => [
                    'route' => 'type_view',
                    'routeParams' => [
                        'versionSlug' => $version->getSlug(),
                        'typeSlug' => function ($pokemonType, $value) {
                            return $pokemonType->getType()->getSlug();
                        },
                    ],
                    'linkClassName' => function ($pokemonType, $value): ?string {
                        return sprintf('pkt-type-emblem-%s', $pokemonType->getType()->getSlug());
                    },
                ],
            ]
        );
        if ($version->getVersionGroup()->hasFeatureString('abilities')) {
            $dataTable->add(
                'abilities',
                CollectionColumn::class,
                [
                    'label' => 'Abilities',
//                    'orderable' => true,
                    'propertyPath' => 'abilities',
                    'className' => 'pkt-pokemon-index-table-ability',
                    'childType' => LinkColumn::class,
                    'childOptions' => [
                        'route' => 'ability_view',
                        'routeParams' => [
                            'versionSlug' => $version->getSlug(),
                            'abilitySlug' => function (PokemonAbility $pokemonAbility) {
                                return $pokemonAbility->getAbility()->getSlug();
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
            );
        }
        $dataTable->add(
            'hp',
            TextColumn::class,
            [
                'label' => 'HP',
                'propertyPath' => 'stats',
//                'orderable' => true,
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
//                'orderable' => true,
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
//                'orderable' => true,
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
//                'orderable' => true,
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
//                'orderable' => true,
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
//                'orderable' => true,
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
                'propertyPath' => 'stats',
//                'orderable' => true,
                'className' => 'pkt-pokemon-index-table-total',
                'data' => function ($context, Collection $value) {
                    $total = 0;
                    foreach ($value as $pokemonStat) {
                        /** @var PokemonStat $pokemonStat */
                        $total += $pokemonStat->getBaseValue();
                    }

                    return $total;
                },
            ]
        )->createAdapter(
            ORMAdapter::class,
            [
                'entity' => Pokemon::class,
                'query' => function (QueryBuilder $qb) use ($version) {
                    $this->query($qb, $version);
                },
            ]
        );
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

    protected function query(QueryBuilder $qb, Version $version): void
    {
        if (!$qb->getDQLPart('from')) {
            $qb->from(Pokemon::class, 'pokemon');
        }
        $qb->distinct()
            ->addSelect('pokemon')
            ->addSelect('pokemon_species')
            ->join('pokemon.species', 'pokemon_species')
            ->join('pokemon_species.versionGroup', 'version_group')
            ->andWhere(':version MEMBER OF version_group.versions')
            ->addOrderBy('pokemon_species.position')
            ->addOrderBy('pokemon.position')
            ->setParameter('version', $version);
    }
}
