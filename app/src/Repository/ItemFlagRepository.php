<?php

namespace App\Repository;

use App\Entity\ItemFlag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method ItemFlag|null find($id, $lockMode = null, $lockVersion = null)
 * @method ItemFlag|null findOneBy(array $criteria, array $orderBy = null)
 * @method ItemFlag[]    findAll()
 * @method ItemFlag[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ItemFlagRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ItemFlag::class);
    }

//    /**
//     * @return ItemFlag[] Returns an array of ItemFlag objects
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
    public function findOneBySomeField($value): ?ItemFlag
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
