<?php

namespace App\Repository;

use App\Entity\MoveTarget;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method MoveTarget|null find($id, $lockMode = null, $lockVersion = null)
 * @method MoveTarget|null findOneBy(array $criteria, array $orderBy = null)
 * @method MoveTarget[]    findAll()
 * @method MoveTarget[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MoveTargetRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, MoveTarget::class);
    }

//    /**
//     * @return MoveTarget[] Returns an array of MoveTarget objects
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
    public function findOneBySomeField($value): ?MoveTarget
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
