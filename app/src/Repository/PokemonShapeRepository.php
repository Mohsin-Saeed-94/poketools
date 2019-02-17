<?php

namespace App\Repository;

use App\Entity\PokemonShape;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method PokemonShape|null find($id, $lockMode = null, $lockVersion = null)
 * @method PokemonShape|null findOneBy(array $criteria, array $orderBy = null)
 * @method PokemonShape[]    findAll()
 * @method PokemonShape[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PokemonShapeRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, PokemonShape::class);
    }

//    /**
//     * @return PokemonShape[] Returns an array of PokemonShape objects
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
    public function findOneBySomeField($value): ?PokemonShape
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
