<?php

namespace App\Repository;

use App\Entity\BattleStyle;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method BattleStyle|null find($id, $lockMode = null, $lockVersion = null)
 * @method BattleStyle|null findOneBy(array $criteria, array $orderBy = null)
 * @method BattleStyle[]    findAll()
 * @method BattleStyle[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BattleStyleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BattleStyle::class);
    }

//    /**
//     * @return BattleStyle[] Returns an array of BattleStyle objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('b.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?BattleStyle
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
