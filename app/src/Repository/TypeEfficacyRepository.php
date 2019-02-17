<?php

namespace App\Repository;

use App\Entity\TypeEfficacy;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method TypeEfficacy|null find($id, $lockMode = null, $lockVersion = null)
 * @method TypeEfficacy|null findOneBy(array $criteria, array $orderBy = null)
 * @method TypeEfficacy[]    findAll()
 * @method TypeEfficacy[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TypeEfficacyRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, TypeEfficacy::class);
    }

//    /**
//     * @return TypeEfficacy[] Returns an array of TypeEfficacy objects
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
    public function findOneBySomeField($value): ?TypeEfficacy
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
