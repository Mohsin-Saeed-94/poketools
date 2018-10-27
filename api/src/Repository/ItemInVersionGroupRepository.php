<?php

namespace App\Repository;

use App\Entity\ItemInVersionGroup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ItemInVersionGroup|null find($id, $lockMode = null, $lockVersion = null)
 * @method ItemInVersionGroup|null findOneBy(array $criteria, array $orderBy = null)
 * @method ItemInVersionGroup[]    findAll()
 * @method ItemInVersionGroup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ItemInVersionGroupRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ItemInVersionGroup::class);
    }

//    /**
//     * @return ItemInVersionGroup[] Returns an array of ItemInVersionGroup objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('i.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ItemInVersionGroup
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
