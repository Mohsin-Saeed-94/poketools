<?php

namespace App\Repository;

use App\Entity\ItemInVersionGroup;
use App\Entity\Pokemon;
use App\Entity\PokemonWildHeldItem;
use App\Entity\Version;
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

    /**
     * @param ItemInVersionGroup $item
     * @param Version $version
     *
     * @return PokemonWildHeldItem[]
     */
    public function findByItemAndVersion(ItemInVersionGroup $item, Version $version)
    {
        $qb = $this->createQueryBuilder('held_item');
        $qb->join('held_item.pokemon', 'pokemon')
            ->join('pokemon.species', 'species')
            ->where('held_item.item = :item')
            ->andWhere('held_item.version = :version')
            ->orderBy('species.position')
            ->addOrderBy('pokemon.position')
            ->addOrderBy('held_item.rate')
            ->setParameter('item', $item)
            ->setParameter('version', $version);

        $q = $qb->getQuery();
        $q->execute();

        return $q->getResult();
    }

    /**
     * @param Pokemon $pokemon
     *
     * @param Version $version
     *
     * @return int
     */
    public function countByPokemon(Pokemon $pokemon, Version $version): int
    {
        $qb = $this->createQueryBuilder('held_item');
        $qb->select('COUNT(held_item.id)')
            ->andWhere('held_item.pokemon = :pokemon')
            ->andWhere('held_item.version = :version')
            ->setParameter('pokemon', $pokemon)
            ->setParameter('version', $version);
        $q = $qb->getQuery();
        $q->execute();

        return $q->getSingleScalarResult();
    }
}
