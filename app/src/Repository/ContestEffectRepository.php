<?php

namespace App\Repository;

use App\Entity\ContestEffect;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method ContestEffect|null find($id, $lockMode = null, $lockVersion = null)
 * @method ContestEffect|null findOneBy(array $criteria, array $orderBy = null)
 * @method ContestEffect[]    findAll()
 * @method ContestEffect[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ContestEffectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ContestEffect::class);
    }

//    /**
//     * @return ContestEffect[] Returns an array of ContestEffect objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ContestEffect
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
