<?php

namespace App\Repository;

use App\Entity\PokemonPalParkData;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PokemonPalParkData|null find($id, $lockMode = null, $lockVersion = null)
 * @method PokemonPalParkData|null findOneBy(array $criteria, array $orderBy = null)
 * @method PokemonPalParkData[]    findAll()
 * @method PokemonPalParkData[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PokemonPalParkDataRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PokemonPalParkData::class);
    }

//    /**
//     * @return PokemonPalParkData[] Returns an array of PokemonPalParkData objects
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
    public function findOneBySomeField($value): ?PokemonPalParkData
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
