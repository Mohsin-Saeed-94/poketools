<?php

namespace App\Repository;

use App\Entity\MoveStatChange;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method MoveStatChange|null find($id, $lockMode = null, $lockVersion = null)
 * @method MoveStatChange|null findOneBy(array $criteria, array $orderBy = null)
 * @method MoveStatChange[]    findAll()
 * @method MoveStatChange[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MoveStatChangeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MoveStatChange::class);
    }

//    /**
//     * @return MoveStatChange[] Returns an array of MoveStatChange objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?MoveStatChange
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
