<?php

namespace App\Repository;

use App\Entity\EggGroup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method EggGroup|null find($id, $lockMode = null, $lockVersion = null)
 * @method EggGroup|null findOneBy(array $criteria, array $orderBy = null)
 * @method EggGroup[]    findAll()
 * @method EggGroup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EggGroupRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, EggGroup::class);
    }

//    /**
//     * @return EggGroup[] Returns an array of EggGroup objects
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
    public function findOneBySomeField($value): ?EggGroup
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
