<?php

namespace App\Repository;

use App\Entity\ContestType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method ContestType|null find($id, $lockMode = null, $lockVersion = null)
 * @method ContestType|null findOneBy(array $criteria, array $orderBy = null)
 * @method ContestType[]    findAll()
 * @method ContestType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ContestTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ContestType::class);
    }

//    /**
//     * @return ContestType[] Returns an array of ContestType objects
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
    public function findOneBySomeField($value): ?ContestType
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
