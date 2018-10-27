<?php

namespace App\Repository;

use App\Entity\BerryFlavorLevel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method BerryFlavorLevel|null find($id, $lockMode = null, $lockVersion = null)
 * @method BerryFlavorLevel|null findOneBy(array $criteria, array $orderBy = null)
 * @method BerryFlavorLevel[]    findAll()
 * @method BerryFlavorLevel[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BerryFlavorLevelRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, BerryFlavorLevel::class);
    }

//    /**
//     * @return BerryFlavorLevel[] Returns an array of BerryFlavorLevel objects
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
    public function findOneBySomeField($value): ?BerryFlavorLevel
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
