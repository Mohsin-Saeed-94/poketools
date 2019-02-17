<?php

namespace App\Repository;

use App\Entity\PokemonAbility;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method PokemonAbility|null find($id, $lockMode = null, $lockVersion = null)
 * @method PokemonAbility|null findOneBy(array $criteria, array $orderBy = null)
 * @method PokemonAbility[]    findAll()
 * @method PokemonAbility[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PokemonAbilityRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, PokemonAbility::class);
    }

//    /**
//     * @return PokemonAbility[] Returns an array of PokemonAbility objects
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
    public function findOneBySomeField($value): ?PokemonAbility
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
