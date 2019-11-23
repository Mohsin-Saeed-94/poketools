<?php

namespace App\Repository;

use App\Entity\NaturePokeathlonStatChange;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method NaturePokeathlonStatChange|null find($id, $lockMode = null, $lockVersion = null)
 * @method NaturePokeathlonStatChange|null findOneBy(array $criteria, array $orderBy = null)
 * @method NaturePokeathlonStatChange[]    findAll()
 * @method NaturePokeathlonStatChange[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NaturePokeathlonStatChangeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NaturePokeathlonStatChange::class);
    }

//    /**
//     * @return NaturePokeathlonStatChange[] Returns an array of NaturePokeathlonStatChange objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('n.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?NaturePokeathlonStatChange
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
