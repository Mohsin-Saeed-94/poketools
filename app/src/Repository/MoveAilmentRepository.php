<?php

namespace App\Repository;

use App\Entity\MoveAilment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method MoveAilment|null find($id, $lockMode = null, $lockVersion = null)
 * @method MoveAilment|null findOneBy(array $criteria, array $orderBy = null)
 * @method MoveAilment[]    findAll()
 * @method MoveAilment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MoveAilmentRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, MoveAilment::class);
    }

//    /**
//     * @return MoveAilment[] Returns an array of MoveAilment objects
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
    public function findOneBySomeField($value): ?MoveAilment
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
