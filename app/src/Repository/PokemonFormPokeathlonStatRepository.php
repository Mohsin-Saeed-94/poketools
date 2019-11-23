<?php

namespace App\Repository;

use App\Entity\PokemonFormPokeathlonStat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method PokemonFormPokeathlonStat|null find($id, $lockMode = null, $lockVersion = null)
 * @method PokemonFormPokeathlonStat|null findOneBy(array $criteria, array $orderBy = null)
 * @method PokemonFormPokeathlonStat[]    findAll()
 * @method PokemonFormPokeathlonStat[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PokemonFormPokeathlonStatRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PokemonFormPokeathlonStat::class);
    }

//    /**
//     * @return PokemonFormPokeathlonStat[] Returns an array of PokemonFormPokeathlonStat objects
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
    public function findOneBySomeField($value): ?PokemonFormPokeathlonStat
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
