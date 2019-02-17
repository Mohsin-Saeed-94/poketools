<?php

namespace App\Repository;

use App\Entity\Berry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Berry|null find($id, $lockMode = null, $lockVersion = null)
 * @method Berry|null findOneBy(array $criteria, array $orderBy = null)
 * @method Berry[]    findAll()
 * @method Berry[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BerryRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Berry::class);
    }

//    /**
//     * @return Berry[] Returns an array of Berry objects
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
    public function findOneBySomeField($value): ?Berry
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
