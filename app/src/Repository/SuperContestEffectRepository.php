<?php

namespace App\Repository;

use App\Entity\SuperContestEffect;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SuperContestEffect|null find($id, $lockMode = null, $lockVersion = null)
 * @method SuperContestEffect|null findOneBy(array $criteria, array $orderBy = null)
 * @method SuperContestEffect[]    findAll()
 * @method SuperContestEffect[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SuperContestEffectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SuperContestEffect::class);
    }

//    /**
//     * @return SuperContestEffect[] Returns an array of SuperContestEffect objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?SuperContestEffect
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
