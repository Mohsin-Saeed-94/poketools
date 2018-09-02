<?php

namespace App\Repository;

use App\Entity\ItemPocket;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ItemPocket|null find($id, $lockMode = null, $lockVersion = null)
 * @method ItemPocket|null findOneBy(array $criteria, array $orderBy = null)
 * @method ItemPocket[]    findAll()
 * @method ItemPocket[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ItemPocketRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ItemPocket::class);
    }

//    /**
//     * @return ItemPocket[] Returns an array of ItemPocket objects
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
    public function findOneBySomeField($value): ?ItemPocket
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
