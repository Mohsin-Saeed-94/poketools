<?php

namespace App\Repository;

use App\Entity\PokemonEvolutionCondition;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method PokemonEvolutionCondition|null find($id, $lockMode = null, $lockVersion = null)
 * @method PokemonEvolutionCondition|null findOneBy(array $criteria, array $orderBy = null)
 * @method PokemonEvolutionCondition[]    findAll()
 * @method PokemonEvolutionCondition[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PokemonEvolutionConditionRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, PokemonEvolutionCondition::class);
    }

//    /**
//     * @return PokemonEvolutionCondition[] Returns an array of PokemonEvolutionCondition objects
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
    public function findOneBySomeField($value): ?PokemonEvolutionCondition
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
