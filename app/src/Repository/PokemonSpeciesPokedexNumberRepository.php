<?php

namespace App\Repository;

use App\Entity\PokemonSpeciesPokedexNumber;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method PokemonSpeciesPokedexNumber|null find($id, $lockMode = null, $lockVersion = null)
 * @method PokemonSpeciesPokedexNumber|null findOneBy(array $criteria, array $orderBy = null)
 * @method PokemonSpeciesPokedexNumber[]    findAll()
 * @method PokemonSpeciesPokedexNumber[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PokemonSpeciesPokedexNumberRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, PokemonSpeciesPokedexNumber::class);
    }

//    /**
//     * @return PokemonPokedexNumber[] Returns an array of PokemonPokedexNumber objects
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
    public function findOneBySomeField($value): ?PokemonPokedexNumber
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
