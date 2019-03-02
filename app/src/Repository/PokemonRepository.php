<?php

namespace App\Repository;

use App\Entity\AbilityInVersionGroup;
use App\Entity\Pokemon;
use App\Entity\Stat;
use App\Entity\Type;
use App\Entity\Version;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Pokemon|null find($id, $lockMode = null, $lockVersion = null)
 * @method Pokemon|null findOneBy(array $criteria, array $orderBy = null)
 * @method Pokemon[]    findAll()
 * @method Pokemon[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PokemonRepository extends ServiceEntityRepository
{
    use PagingTrait;

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Pokemon::class);
    }

//    /**
//     * @return Pokemon[] Returns an array of Pokemon objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Pokemon
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    /**
     * @param Version $version
     * @param int $start
     * @param int $limit
     *
     * @return Pokemon[]
     */
    public function findWithVersion(Version $version, int $start = 0, int $limit = 0): array
    {
        $qb = $this->createQueryBuilder('pokemon');
        $this->filterWithVersion($qb, $version);
        $this->pageQuery($qb, $start, $limit);

        $q = $qb->getQuery();
        $q->execute();

        return $q->getResult();
    }

    /**
     * @param QueryBuilder $qb
     * @param Version $version
     */
    protected function filterWithVersion(QueryBuilder $qb, Version $version): void
    {
        $qb->join('pokemon.species', 'pokemon_species')
            ->join('pokemon_species.versionGroup', 'version_group')
            ->andWhere(':version MEMBER OF version_group.versions')
            ->setParameter('version', $version);
    }

    /**
     * @param Version $version
     *
     * @return int
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function countWithVersion(Version $version): int
    {
        $qb = $this->createQueryBuilder('pokemon');
        $qb->select('COUNT(pokemon.id)');
        $this->filterWithVersion($qb, $version);

        $q = $qb->getQuery();
        $q->execute();

        return (int)$q->getSingleScalarResult();
    }

    /**
     * @param AbilityInVersionGroup $abilityInVersionGroup
     *
     * @param int $start
     * @param int $limit
     *
     * @return Pokemon[]
     */
    public function findWithAbility(AbilityInVersionGroup $abilityInVersionGroup, int $start = 0, int $limit = 0): array
    {
        $qb = $this->createQueryBuilder('pokemon');
        $this->filterWithAbility($qb, $abilityInVersionGroup);
        $qb->addOrderBy('pokemon_species.position')
            ->addOrderBy('pokemon.position');
        $this->pageQuery($qb, $start, $limit);

        $q = $qb->getQuery();
        $q->execute();

        return $q->getResult();
    }

    /**
     * @param QueryBuilder $qb
     * @param AbilityInVersionGroup $abilityInVersionGroup
     */
    protected function filterWithAbility(QueryBuilder $qb, AbilityInVersionGroup $abilityInVersionGroup): void
    {
        $qb->join('pokemon.abilities', 'pokemon_abilities')
            ->join('pokemon.species', 'pokemon_species')
            ->andWhere('pokemon_abilities.ability = :ability')
            ->setParameter('ability', $abilityInVersionGroup);
    }

    /**
     * @param AbilityInVersionGroup $abilityInVersionGroup
     *
     * @return int
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function countWithAbility(AbilityInVersionGroup $abilityInVersionGroup): int
    {
        $qb = $this->createQueryBuilder('pokemon');
        $qb->select('COUNT(pokemon.id)');
        $this->filterWithAbility($qb, $abilityInVersionGroup);

        $q = $qb->getQuery();
        $q->execute();

        return (int)$q->getSingleScalarResult();
    }

    /**
     * @param Version $version
     * @param Stat $highStat
     *   The stat that should be the highest
     * @param Stat $lowStat
     *   The stat that should be the lowest.
     *
     * @return array
     */
    public function findMatchingStats(Version $version, Stat $highStat, Stat $lowStat): array
    {
        $qb = $this->createQueryBuilder('pokemon');
        $qb->addSelect('pokemon_stats')
            ->join('pokemon.stats', 'pokemon_stats')
            ->join('pokemon.species', 'species')
            ->where('species.versionGroup = :versionGroup')
            ->orderBy('species.position')
            ->addOrderBy('pokemon.position')
            ->setParameter('versionGroup', $version->getVersionGroup());

        $q = $qb->getQuery();
        $q->execute();
        /** @var Pokemon[] $results */
        $results = $q->getResult();

        // Filter the results down.
        if ($highStat === $lowStat) {
            // The largest standard deviation for stats to be considered "similar"
            $deviationLimit = 7;
            $filterCallback = function (Pokemon $pokemon) use ($deviationLimit) {
                $statVals = [];
                foreach ($pokemon->getStats() as $pokemonStat) {
                    if ($pokemonStat->getStat()->getSlug() === 'hp') {
                        // Ignore HP.
                        continue;
                    }
                    $statVals[] = $pokemonStat->getBaseValue();
                }
                $statDeviation = $this->standardDeviation($statVals);

                return $statDeviation <= $deviationLimit;
            };
        } else {
            $filterCallback = function (Pokemon $pokemon) use ($highStat, $lowStat) {
                $pokemonHighStat = null;
                $pokemonHighStatVal = PHP_INT_MIN;
                $pokemonLowStat = null;
                $pokemonLowStatVal = PHP_INT_MAX;
                foreach ($pokemon->getStats() as $pokemonStat) {
                    if ($pokemonStat->getStat()->getSlug() === 'hp') {
                        // Ignore HP.
                        continue;
                    }
                    if ($pokemonStat->getBaseValue() > $pokemonHighStatVal) {
                        $pokemonHighStat = $pokemonStat->getStat();
                        $pokemonHighStatVal = $pokemonStat->getBaseValue();
                    }
                    if ($pokemonStat->getBaseValue() < $pokemonLowStatVal) {
                        $pokemonLowStat = $pokemonStat->getStat();
                        $pokemonLowStatVal = $pokemonStat->getBaseValue();
                    }
                }

                return $pokemonHighStat === $highStat && $pokemonLowStat === $lowStat;
            };
        }
        $results = array_filter($results, $filterCallback);

        return $results;
    }

    /**
     * Polyfill for stats_standard_deviation()
     *
     * @see http://php.net/manual/en/function.stats-standard-deviation.php
     *
     * @param array $set
     *
     * @return int
     *
     * @throws \InvalidArgumentException
     *   Thrown when $set is empty.
     */
    private function standardDeviation(array $set): int
    {
        if (function_exists('stats_standard_deviation')) {
            return stats_standard_deviation($set);
        }

        $n = count($set);
        if ($n === 0) {
            throw new \InvalidArgumentException('The array has zero elements');
        }
        $mean = array_sum($set) / $n;
        $carry = 0.0;
        foreach ($set as $val) {
            $d = ((double)$val) - $mean;
            $carry += $d * $d;
        }

        return sqrt($carry / $n);
    }

    /**
     * @param Version $version
     * @param Type $type
     * @param int $start
     * @param int $limit
     *
     * @return Pokemon[]
     */
    public function findWithType(Version $version, Type $type, int $start = 0, int $limit = 0): array
    {
        $qb = $this->createQueryBuilder('pokemon');
        $this->filterWithType($qb, $version, $type);
        $qb->addOrderBy('pokemon_species.position')
            ->addOrderBy('pokemon.position');
        $this->pageQuery($qb, $start, $limit);

        $q = $qb->getQuery();
        $q->execute();

        return $q->getResult();
    }

    /**
     * @param QueryBuilder $qb
     * @param Version $version
     * @param Type $type
     */
    protected function filterWithType(QueryBuilder $qb, Version $version, Type $type): void
    {
        $this->filterWithVersion($qb, $version);
        $qb->join('pokemon.types', 'pokemon_types')
            ->join('pokemon_types.type', 'type')
            ->andWhere(':type = type')
            ->setParameter('type', $type);
    }

    /**
     * @param Version $version
     * @param Type $type
     *
     * @return int
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function countWithType(Version $version, Type $type): int
    {
        $qb = $this->createQueryBuilder('pokemon');
        $qb->select('COUNT(pokemon.id)');
        $this->filterWithType($qb, $version, $type);

        $q = $qb->getQuery();
        $q->execute();

        return (int)$q->getSingleScalarResult();
    }
}
