<?php

namespace App\Repository;

use App\Entity\TimeOfDay;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method TimeOfDay|null find($id, $lockMode = null, $lockVersion = null)
 * @method TimeOfDay|null findOneBy(array $criteria, array $orderBy = null)
 * @method TimeOfDay[]    findAll()
 * @method TimeOfDay[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TimeOfDayRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, TimeOfDay::class);
    }

//    /**
//     * @return TimeOfDayInVersionGroup[] Returns an array of TimeOfDayInVersionGroup objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?TimeOfDayInVersionGroup
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
