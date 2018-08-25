<?php

namespace App\Repository;

use App\Entity\PokeathlonStat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method PokeathlonStat|null find($id, $lockMode = null, $lockVersion = null)
 * @method PokeathlonStat|null findOneBy(array $criteria, array $orderBy = null)
 * @method PokeathlonStat[]    findAll()
 * @method PokeathlonStat[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PokeathlonStatRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, PokeathlonStat::class);
    }

//    /**
//     * @return PokeathlonStat[] Returns an array of PokeathlonStat objects
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
    public function findOneBySomeField($value): ?PokeathlonStat
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
