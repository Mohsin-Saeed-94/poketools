<?php

namespace App\Repository;

use App\Entity\PokemonMoveLevelUp;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method PokemonMoveLevelUp|null find($id, $lockMode = null, $lockVersion = null)
 * @method PokemonMoveLevelUp|null findOneBy(array $criteria, array $orderBy = null)
 * @method PokemonMoveLevelUp[]    findAll()
 * @method PokemonMoveLevelUp[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PokemonMoveLevelUpRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PokemonMoveLevelUp::class);
    }

//    /**
//     * @return PokemonMoveLevelUp[] Returns an array of PokemonMoveLevelUp objects
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
    public function findOneBySomeField($value): ?PokemonMoveLevelUp
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
