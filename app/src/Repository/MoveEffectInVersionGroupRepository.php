<?php

namespace App\Repository;

use App\Entity\MoveEffectInVersionGroup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method MoveEffectInVersionGroup|null find($id, $lockMode = null, $lockVersion = null)
 * @method MoveEffectInVersionGroup|null findOneBy(array $criteria, array $orderBy = null)
 * @method MoveEffectInVersionGroup[]    findAll()
 * @method MoveEffectInVersionGroup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MoveEffectInVersionGroupRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MoveEffectInVersionGroup::class);
    }

//    /**
//     * @return MoveEffectInVersionGroup[] Returns an array of MoveEffectInVersionGroup objects
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
    public function findOneBySomeField($value): ?MoveEffectInVersionGroup
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
