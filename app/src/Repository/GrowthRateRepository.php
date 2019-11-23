<?php

namespace App\Repository;

use App\Entity\GrowthRate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method GrowthRate|null find($id, $lockMode = null, $lockVersion = null)
 * @method GrowthRate|null findOneBy(array $criteria, array $orderBy = null)
 * @method GrowthRate[]    findAll()
 * @method GrowthRate[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GrowthRateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GrowthRate::class);
    }

//    /**
//     * @return GrowthRate[] Returns an array of GrowthRate objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('g.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?GrowthRate
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
