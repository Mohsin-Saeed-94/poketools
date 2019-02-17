<?php

namespace App\Repository;

use App\Entity\PokemonFlavorText;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method PokemonFlavorText|null find($id, $lockMode = null, $lockVersion = null)
 * @method PokemonFlavorText|null findOneBy(array $criteria, array $orderBy = null)
 * @method PokemonFlavorText[]    findAll()
 * @method PokemonFlavorText[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PokemonFlavorTextRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, PokemonFlavorText::class);
    }

//    /**
//     * @return PokemonFlavorText[] Returns an array of PokemonFlavorText objects
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
    public function findOneBySomeField($value): ?PokemonFlavorText
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
