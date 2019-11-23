<?php

namespace App\Repository;

use App\Entity\PokemonEvolutionConditionSet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method PokemonEvolutionConditionSet|null find($id, $lockMode = null, $lockVersion = null)
 * @method PokemonEvolutionConditionSet|null findOneBy(array $criteria, array $orderBy = null)
 * @method PokemonEvolutionConditionSet[]    findAll()
 * @method PokemonEvolutionConditionSet[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PokemonEvolutionConditionSetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PokemonEvolutionConditionSet::class);
    }

//    /**
//     * @return PokemonEvolutionConditionSet[] Returns an array of PokemonEvolutionConditionSet objects
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
    public function findOneBySomeField($value): ?PokemonEvolutionConditionSet
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
