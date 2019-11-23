<?php

namespace App\Repository;

use App\Entity\NatureBattleStylePreference;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method NatureBattleStylePreference|null find($id, $lockMode = null, $lockVersion = null)
 * @method NatureBattleStylePreference|null findOneBy(array $criteria, array $orderBy = null)
 * @method NatureBattleStylePreference[]    findAll()
 * @method NatureBattleStylePreference[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NatureBattleStylePreferenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NatureBattleStylePreference::class);
    }

//    /**
//     * @return NatureBattleStylePreference[] Returns an array of NatureBattleStylePreference objects
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
    public function findOneBySomeField($value): ?NatureBattleStylePreference
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
