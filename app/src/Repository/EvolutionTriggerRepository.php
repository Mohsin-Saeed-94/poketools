<?php

namespace App\Repository;

use App\Entity\EvolutionTrigger;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EvolutionTrigger|null find($id, $lockMode = null, $lockVersion = null)
 * @method EvolutionTrigger|null findOneBy(array $criteria, array $orderBy = null)
 * @method EvolutionTrigger[]    findAll()
 * @method EvolutionTrigger[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EvolutionTriggerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EvolutionTrigger::class);
    }

//    /**
//     * @return EvolutionTrigger[] Returns an array of EvolutionTrigger objects
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
    public function findOneBySomeField($value): ?EvolutionTrigger
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
