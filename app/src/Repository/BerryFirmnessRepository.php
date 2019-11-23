<?php

namespace App\Repository;

use App\Entity\BerryFirmness;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method BerryFirmness|null find($id, $lockMode = null, $lockVersion = null)
 * @method BerryFirmness|null findOneBy(array $criteria, array $orderBy = null)
 * @method BerryFirmness[]    findAll()
 * @method BerryFirmness[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BerryFirmnessRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BerryFirmness::class);
    }

//    /**
//     * @return BerryFirmness[] Returns an array of BerryFirmness objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('b.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?BerryFirmness
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
