<?php

namespace App\Repository;

use App\Entity\PokeblockColor;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method PokeblockColor|null find($id, $lockMode = null, $lockVersion = null)
 * @method PokeblockColor|null findOneBy(array $criteria, array $orderBy = null)
 * @method PokeblockColor[]    findAll()
 * @method PokeblockColor[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PokeblockColorRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, PokeblockColor::class);
    }

//    /**
//     * @return PokeblockColor[] Returns an array of PokeblockColor objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?PokeblockColor
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
