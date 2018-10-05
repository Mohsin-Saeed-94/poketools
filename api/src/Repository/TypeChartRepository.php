<?php

namespace App\Repository;

use App\Entity\TypeChart;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method TypeChart|null find($id, $lockMode = null, $lockVersion = null)
 * @method TypeChart|null findOneBy(array $criteria, array $orderBy = null)
 * @method TypeChart[]    findAll()
 * @method TypeChart[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TypeChartRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, TypeChart::class);
    }

//    /**
//     * @return TypeChart[] Returns an array of TypeChart objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?TypeChart
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
