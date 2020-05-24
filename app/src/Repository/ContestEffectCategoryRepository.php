<?php

namespace App\Repository;

use App\Entity\ContestEffectCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ContestEffectCategory|null find($id, $lockMode = null, $lockVersion = null)
 * @method ContestEffectCategory|null findOneBy(array $criteria, array $orderBy = null)
 * @method ContestEffectCategory[]    findAll()
 * @method ContestEffectCategory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ContestEffectCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ContestEffectCategory::class);
    }

    // /**
    //  * @return ContestEffectCategory[] Returns an array of ContestEffectCategory objects
    //  */
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
    public function findOneBySomeField($value): ?ContestEffectCategory
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
