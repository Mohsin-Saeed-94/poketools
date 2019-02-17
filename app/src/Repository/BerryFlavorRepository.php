<?php

namespace App\Repository;

use App\Entity\BerryFlavor;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method BerryFlavor|null find($id, $lockMode = null, $lockVersion = null)
 * @method BerryFlavor|null findOneBy(array $criteria, array $orderBy = null)
 * @method BerryFlavor[]    findAll()
 * @method BerryFlavor[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BerryFlavorRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, BerryFlavor::class);
    }

//    /**
//     * @return BerryFlavor[] Returns an array of BerryFlavor objects
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
    public function findOneBySomeField($value): ?BerryFlavor
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
