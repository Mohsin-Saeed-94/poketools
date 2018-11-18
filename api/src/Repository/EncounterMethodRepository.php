<?php

namespace App\Repository;

use App\Entity\EncounterMethod;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method EncounterMethod|null find($id, $lockMode = null, $lockVersion = null)
 * @method EncounterMethod|null findOneBy(array $criteria, array $orderBy = null)
 * @method EncounterMethod[]    findAll()
 * @method EncounterMethod[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EncounterMethodRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, EncounterMethod::class);
    }

    // /**
    //  * @return EncounterMethod[] Returns an array of EncounterMethod objects
    //  */
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
    public function findOneBySomeField($value): ?EncounterMethod
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
