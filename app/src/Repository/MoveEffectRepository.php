<?php

namespace App\Repository;

use App\Entity\MoveEffect;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method MoveEffect|null find($id, $lockMode = null, $lockVersion = null)
 * @method MoveEffect|null findOneBy(array $criteria, array $orderBy = null)
 * @method MoveEffect[]    findAll()
 * @method MoveEffect[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MoveEffectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MoveEffect::class);
    }

//    /**
//     * @return MoveEffect[] Returns an array of MoveEffect objects
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
    public function findOneBySomeField($value): ?MoveEffect
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
