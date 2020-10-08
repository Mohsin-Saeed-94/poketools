<?php

namespace App\Repository;

use App\Entity\PokemonStat;
use App\Entity\Version;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PokemonStat|null find($id, $lockMode = null, $lockVersion = null)
 * @method PokemonStat|null findOneBy(array $criteria, array $orderBy = null)
 * @method PokemonStat[]    findAll()
 * @method PokemonStat[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PokemonStatRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PokemonStat::class);
    }

//    /**
//     * @return PokemonStat[] Returns an array of PokemonStat objects
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
    public function findOneBySomeField($value): ?PokemonStat
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
     * @param PokemonStat|int $pokemonStat
     *   Pass an int to calculate the total stat percentile.
     *
     * @return int
     */
    public function calcPercentileForStat(Version $version, $pokemonStat): int
    {
        // Count Pokemon with a value less than the given stat
        $lessQb = $this->createQueryBuilder('pokemon_stat');
        $lessQb->join('pokemon_stat.pokemon', 'pokemon')
            ->join('pokemon.species', 'species')
            ->join('species.versionGroup', 'version_group')
            ->andWhere(':version MEMBER OF version_group.versions')
            ->setParameter('version', $version);
        if ($pokemonStat instanceof PokemonStat) {
            $lessQb->select('COUNT(pokemon_stat.pokemon)')
                ->andWhere('pokemon_stat.stat = :stat')
                ->andWhere('pokemon_stat.baseValue < :value')
                ->setParameter('stat', $pokemonStat->getStat())
                ->setParameter('value', $pokemonStat->getBaseValue());
            $lessQ = $lessQb->getQuery();
            $lessQ->execute();
            $less = (int)$lessQ->getSingleScalarResult();
        } else {
            $lessQb->select('pokemon.id')
                ->groupBy('pokemon.id')
                ->andHaving('SUM(pokemon_stat.baseValue) < :value')
                ->setParameter('value', $pokemonStat);
            $lessQ = $lessQb->getQuery();
            $lessQ->execute();
            $less = count($lessQ->getResult());
        }

        // Count Pokemon with a value equal to the given stat
        $equalQb = $this->createQueryBuilder('pokemon_stat');
        $equalQb->join('pokemon_stat.pokemon', 'pokemon')
            ->join('pokemon.species', 'species')
            ->join('species.versionGroup', 'version_group')
            ->andWhere(':version MEMBER OF version_group.versions')
            ->setParameter('version', $version);
        if ($pokemonStat instanceof PokemonStat) {
            $equalQb->select('COUNT(pokemon_stat.pokemon)')
                ->andWhere('pokemon_stat.stat = :stat')
                ->andWhere('pokemon_stat.baseValue = :value')
                ->setParameter('stat', $pokemonStat->getStat())
                ->setParameter('value', $pokemonStat->getBaseValue());
            $equalQ = $equalQb->getQuery();
            $equalQ->execute();
            $equal = (int)$equalQ->getSingleScalarResult();
        } else {
            $equalQb->select('pokemon.id')
                ->groupBy('pokemon.id')
                ->andHaving('SUM(pokemon_stat.baseValue) = :value')
                ->setParameter('value', $pokemonStat);
            $equalQ = $equalQb->getQuery();
            $equalQ->execute();
            $equal = count($equalQ->getResult());
        }


        // Count the number of rows applicable
        $countQb = $this->createQueryBuilder('pokemon_stat');
        $countQb->select('COUNT(pokemon_stat.stat)')
            ->distinct()
            ->join('pokemon_stat.pokemon', 'pokemon')
            ->join('pokemon.species', 'species')
            ->join('species.versionGroup', 'version_group')
            ->groupBy('pokemon_stat.stat')
            ->andWhere(':version MEMBER OF version_group.versions')
            ->setParameter('version', $version);
        $countQ = $countQb->getQuery();
        $countQ->execute();
        $count = (int)$countQ->getSingleScalarResult();

        $percentile = (($less + ($equal / 2.0)) / $count) * 100;

        return $percentile;
    }
}
