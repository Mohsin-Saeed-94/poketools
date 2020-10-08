<?php

namespace App\Repository;

use App\Entity\PalParkArea;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PalParkArea|null find($id, $lockMode = null, $lockVersion = null)
 * @method PalParkArea|null findOneBy(array $criteria, array $orderBy = null)
 * @method PalParkArea[]    findAll()
 * @method PalParkArea[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PalParkAreaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PalParkArea::class);
    }

//    /**
//     * @return PalParkArea[] Returns an array of PalParkArea objects
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
    public function findOneBySomeField($value): ?PalParkArea
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
