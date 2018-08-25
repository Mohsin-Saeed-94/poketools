<?php

namespace App\Repository;

use App\Entity\PokemonColor;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method PokemonColor|null find($id, $lockMode = null, $lockVersion = null)
 * @method PokemonColor|null findOneBy(array $criteria, array $orderBy = null)
 * @method PokemonColor[]    findAll()
 * @method PokemonColor[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PokemonColorRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, PokemonColor::class);
    }

//    /**
//     * @return PokemonColor[] Returns an array of PokemonColor objects
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
    public function findOneBySomeField($value): ?PokemonColor
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
