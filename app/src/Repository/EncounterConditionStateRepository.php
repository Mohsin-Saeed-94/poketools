<?php

namespace App\Repository;

use App\Entity\EncounterConditionState;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method EncounterConditionState|null find($id, $lockMode = null, $lockVersion = null)
 * @method EncounterConditionState|null findOneBy(array $criteria, array $orderBy = null)
 * @method EncounterConditionState[]    findAll()
 * @method EncounterConditionState[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EncounterConditionStateRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, EncounterConditionState::class);
    }

//    /**
//     * @return EncounterConditionState[] Returns an array of EncounterConditionState objects
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
    public function findOneBySomeField($value): ?EncounterConditionState
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
