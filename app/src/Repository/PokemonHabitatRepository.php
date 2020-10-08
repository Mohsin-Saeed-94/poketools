<?php

namespace App\Repository;

use App\Entity\PokemonHabitat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PokemonHabitat|null find($id, $lockMode = null, $lockVersion = null)
 * @method PokemonHabitat|null findOneBy(array $criteria, array $orderBy = null)
 * @method PokemonHabitat[]    findAll()
 * @method PokemonHabitat[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PokemonHabitatRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PokemonHabitat::class);
    }

//    /**
//     * @return PokemonHabitat[] Returns an array of PokemonHabitat objects
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
    public function findOneBySomeField($value): ?PokemonHabitat
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
