<?php

namespace App\Repository;

use App\Entity\PokemonShapeInVersionGroup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method PokemonShapeInVersionGroup|null find($id, $lockMode = null, $lockVersion = null)
 * @method PokemonShapeInVersionGroup|null findOneBy(array $criteria, array $orderBy = null)
 * @method PokemonShapeInVersionGroup[]    findAll()
 * @method PokemonShapeInVersionGroup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PokemonShapeInVersionGroupRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PokemonShapeInVersionGroup::class);
    }

//    /**
//     * @return PokemonShapeInVersionGroup[] Returns an array of PokemonShapeInVersionGroup objects
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
    public function findOneBySomeField($value): ?PokemonShapeInVersionGroup
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
