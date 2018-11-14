<?php

namespace App\Repository;

use App\Entity\PokemonWildHeldItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method PokemonWildHeldItem|null find($id, $lockMode = null, $lockVersion = null)
 * @method PokemonWildHeldItem|null findOneBy(array $criteria, array $orderBy = null)
 * @method PokemonWildHeldItem[]    findAll()
 * @method PokemonWildHeldItem[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PokemonWildHeldItemRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, PokemonWildHeldItem::class);
    }

//    /**
//     * @return PokemonWildHeldItem[] Returns an array of PokemonWildHeldItem objects
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
    public function findOneBySomeField($value): ?PokemonWildHeldItem
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