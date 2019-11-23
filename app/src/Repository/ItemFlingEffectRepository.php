<?php

namespace App\Repository;

use App\Entity\ItemFlingEffect;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method ItemFlingEffect|null find($id, $lockMode = null, $lockVersion = null)
 * @method ItemFlingEffect|null findOneBy(array $criteria, array $orderBy = null)
 * @method ItemFlingEffect[]    findAll()
 * @method ItemFlingEffect[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ItemFlingEffectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ItemFlingEffect::class);
    }

//    /**
//     * @return ItemFlingEffect[] Returns an array of ItemFlingEffect objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('i.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ItemFlingEffect
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
