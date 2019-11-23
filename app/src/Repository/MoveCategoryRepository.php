<?php

namespace App\Repository;

use App\Entity\MoveCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method MoveCategory|null find($id, $lockMode = null, $lockVersion = null)
 * @method MoveCategory|null findOneBy(array $criteria, array $orderBy = null)
 * @method MoveCategory[]    findAll()
 * @method MoveCategory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MoveCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MoveCategory::class);
    }

//    /**
//     * @return MoveCategory[] Returns an array of MoveCategory objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?MoveCategory
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
