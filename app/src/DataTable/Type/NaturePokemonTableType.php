<?php

namespace App\DataTable\Type;


use App\DataTable\Adapter\ProcessedOrmAdapter;
use App\Entity\Nature;
use App\Entity\Pokemon;
use App\Entity\Version;
use Doctrine\ORM\QueryBuilder;
use Omines\DataTablesBundle\DataTable;

/**
 * Pokemon Table for Nature view
 */
class NaturePokemonTableType extends PokemonTableType
{
    protected const IDEAL_POKEMON_STAT_DIFF = 10;

    /**
     * {@inheritdoc}
     */
    public function configure(DataTable $dataTable, array $options)
    {
        parent::configure($dataTable, $options);

        /** @var Version $version */
        $version = $options['version'];
        /** @var Nature $nature */
        $nature = $options['nature'];

        $dataTable->setName(self::class)->createAdapter(
            ProcessedOrmAdapter::class,
            [
                'entity' => Pokemon::class,
                'query' => function (QueryBuilder $qb) use ($version, $nature) {
                    $this->query($qb, $version);
                },
                'processor' => function (Pokemon $pokemon) use ($nature) {
                    // The algorithm borrowed from Veekun because it makes sense.
                    if ($nature->getStatIncreased() === $nature->getStatDecreased()) {
                        // "Neutral" natures are a good fit for Pokemon with highest and lowest stats at most 10 apart.
                        $min = PHP_INT_MAX;
                        $max = PHP_INT_MIN;
                        foreach ($pokemon->getStats() as $pokemonStat) {
                            if ($pokemonStat->getStat()->getSlug() === 'hp') {
                                // Ignore HP
                                continue;
                            }
                            $min = min($min, $pokemonStat->getBaseValue());
                            $max = max($max, $pokemonStat->getBaseValue());
                        }
                        if ($max - $min <= self::IDEAL_POKEMON_STAT_DIFF) {
                            return $pokemon;
                        }
                    } else {
                        // Best fit for:
                        // - Highest and lowest stats are more than 10 apart.
                        // - Highest stat is improved by this nature
                        // - Lowest stat is hindered by this nature.
                        $min = PHP_INT_MAX;
                        $minStats = [];
                        $max = PHP_INT_MIN;
                        $maxStats = [];
                        foreach ($pokemon->getStats() as $pokemonStat) {
                            if ($pokemonStat->getStat()->getSlug() === 'hp') {
                                // Ignore HP
                                continue;
                            }
                            if ($pokemonStat->getBaseValue() < $min) {
                                $minStats = [$pokemonStat->getStat()];
                                $min = $pokemonStat->getBaseValue();
                            } elseif ($pokemonStat->getBaseValue() === $min) {
                                $minStats[] = $pokemonStat->getStat();
                            }
                            if ($pokemonStat->getBaseValue() > $max) {
                                $maxStats = [$pokemonStat->getStat()];
                                $max = $pokemonStat->getBaseValue();
                            } elseif ($pokemonStat->getBaseValue() === $max) {
                                $maxStats[] = $pokemonStat->getStat();
                            }
                        }
                        if ($max - $min > self::IDEAL_POKEMON_STAT_DIFF
                            && in_array($nature->getStatIncreased(), $maxStats, true)
                            && in_array($nature->getStatDecreased(), $minStats, true)) {
                            return $pokemon;
                        }
                    }

                    return null;
                },
            ]
        );
    }

}
