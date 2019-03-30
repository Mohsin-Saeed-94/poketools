<?php

namespace App\Repository;

use App\Entity\LocationMap;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method LocationMap|null find($id, $lockMode = null, $lockVersion = null)
 * @method LocationMap|null findOneBy(array $criteria, array $orderBy = null)
 * @method LocationMap[]    findAll()
 * @method LocationMap[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LocationMapRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, LocationMap::class);
    }

    // /**
    //  * @return LocationMap[] Returns an array of LocationMap objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('l.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?LocationMap
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
