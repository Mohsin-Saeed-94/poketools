<?php

namespace App\Repository;

use App\Entity\EncounterCondition;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EncounterCondition|null find($id, $lockMode = null, $lockVersion = null)
 * @method EncounterCondition|null findOneBy(array $criteria, array $orderBy = null)
 * @method EncounterCondition[]    findAll()
 * @method EncounterCondition[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EncounterConditionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EncounterCondition::class);
    }

//    /**
//     * @return EncounterCondition[] Returns an array of EncounterCondition objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?EncounterCondition
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
