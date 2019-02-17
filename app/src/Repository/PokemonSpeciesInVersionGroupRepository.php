<?php

namespace App\Repository;

use App\Entity\PokemonSpeciesInVersionGroup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method PokemonSpeciesInVersionGroup|null find($id, $lockMode = null, $lockVersion = null)
 * @method PokemonSpeciesInVersionGroup|null findOneBy(array $criteria, array $orderBy = null)
 * @method PokemonSpeciesInVersionGroup[]    findAll()
 * @method PokemonSpeciesInVersionGroup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PokemonSpeciesInVersionGroupRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, PokemonSpeciesInVersionGroup::class);
    }

//    /**
//     * @return PokemonSpeciesInVersionGroup[] Returns an array of PokemonSpeciesInVersionGroup objects
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
    public function findOneBySomeField($value): ?PokemonSpeciesInVersionGroup
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
