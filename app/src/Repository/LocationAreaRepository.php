<?php

namespace App\Repository;

use App\Entity\LocationArea;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method LocationArea|null find($id, $lockMode = null, $lockVersion = null)
 * @method LocationArea|null findOneBy(array $criteria, array $orderBy = null)
 * @method LocationArea[]    findAll()
 * @method LocationArea[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LocationAreaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LocationArea::class);
    }

//    /**
//     * @return LocationArea[] Returns an array of LocationArea objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('l.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?LocationArea
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
