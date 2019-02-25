<?php

namespace App\Repository;

use App\Entity\AbilityInVersionGroup;
use App\Entity\Pokemon;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Pokemon|null find($id, $lockMode = null, $lockVersion = null)
 * @method Pokemon|null findOneBy(array $criteria, array $orderBy = null)
 * @method Pokemon[]    findAll()
 * @method Pokemon[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PokemonRepository extends ServiceEntityRepository
{
    use PagingTrait;

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Pokemon::class);
    }

//    /**
//     * @return Pokemon[] Returns an array of Pokemon objects
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
    public function findOneBySomeField($value): ?Pokemon
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
     * @param AbilityInVersionGroup $abilityInVersionGroup
     *
     * @param int $start
     * @param int $limit
     *
     * @return Pokemon[]
     */
    public function findWithAbility(AbilityInVersionGroup $abilityInVersionGroup, int $start = 0, int $limit = 0): array
    {
        $qb = $this->createQueryBuilder('pokemon');
        $this->filterWithAbility($qb, $abilityInVersionGroup);
        $qb->addOrderBy('pokemon_species.position')
            ->addOrderBy('pokemon.position');
        $this->pageQuery($qb, $start, $limit);

        $q = $qb->getQuery();
        $q->execute();

        return $q->getResult();
    }

    /**
     * @param QueryBuilder $qb
     * @param AbilityInVersionGroup $abilityInVersionGroup
     */
    protected function filterWithAbility(QueryBuilder $qb, AbilityInVersionGroup $abilityInVersionGroup)
    {
        $qb->join('pokemon.abilities', 'pokemon_abilities')
            ->join('pokemon.species', 'pokemon_species')
            ->andWhere('pokemon_abilities.ability = :ability')
            ->setParameter('ability', $abilityInVersionGroup);
    }

    /**
     * @param AbilityInVersionGroup $abilityInVersionGroup
     *
     * @return int
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function countWithAbility(AbilityInVersionGroup $abilityInVersionGroup): int
    {
        $qb = $this->createQueryBuilder('pokemon');
        $qb->select('COUNT(pokemon.id)');
        $this->filterWithAbility($qb, $abilityInVersionGroup);

        $q = $qb->getQuery();
        $q->execute();

        return (int)$q->getSingleScalarResult();
    }
}
