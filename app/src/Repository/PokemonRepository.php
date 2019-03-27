<?php

namespace App\Repository;

use App\Entity\Pokemon;
use App\Entity\PokemonSpeciesInVersionGroup;
use App\Entity\Version;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Tree\Entity\Repository\MaterializedPathRepository;
use LogicException;

/**
 * @method Pokemon|null find($id, $lockMode = null, $lockVersion = null)
 * @method Pokemon|null findOneBy(array $criteria, array $orderBy = null)
 * @method Pokemon[]    findAll()
 * @method Pokemon[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PokemonRepository extends MaterializedPathRepository implements ServiceEntityRepositoryInterface, SlugAndVersionInterface
{
    use PagingTrait;

    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        $entityClass = Pokemon::class;
        /** @var EntityManagerInterface $manager */
        $manager = $registry->getManagerForClass($entityClass);

        if ($manager === null) {
            throw new LogicException(
                sprintf(
                    'Could not find the entity manager for class "%s". Check your Doctrine configuration to make sure it is configured to load this entity’s metadata.',
                    $entityClass
                )
            );
        }

        parent::__construct($manager, $manager->getClassMetadata($entityClass));
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
     * {@inheritdoc}
     */
    public function findOneByVersion(string $slug, Version $version): ?Pokemon
    {
        $qb = $this->createQueryBuilder('pokemon');
        $qb->join('pokemon.species', 'species')
            ->join('species.versionGroup', 'version_group')
            ->andWhere('pokemon.slug = :slug')
            ->andWhere(':version MEMBER OF version_group.versions')
            ->setParameter('slug', $slug)
            ->setParameter('version', $version);
        $q = $qb->getQuery();
        $q->execute();

        return $q->getOneOrNullResult();
    }

    /**
     * @param PokemonSpeciesInVersionGroup $species
     * @param Version $version
     * @param string|null $pokemonSlug
     *   Pass null to find the default pokemon for the species.
     *
     * @return Pokemon|null
     */
    public function findOneBySpecies(
        PokemonSpeciesInVersionGroup $species,
        Version $version,
        ?string $pokemonSlug
    ): ?Pokemon {
        $qb = $this->createQueryBuilder('pokemon');
        $qb->join('pokemon.species', 'species')
            ->join('species.versionGroup', 'version_group')
            ->andWhere('pokemon.species = :species')
            ->andWhere(':version MEMBER OF version_group.versions')
            ->setParameter('species', $species)
            ->setParameter('version', $version);

        if ($pokemonSlug === null) {
            $qb->andWhere('pokemon.isDefault = true');
        } else {
            $qb->andWhere('pokemon.slug = :slug')
                ->setParameter('slug', $pokemonSlug);
        }

        $q = $qb->getQuery();
        $q->execute();

        return $q->getOneOrNullResult();
    }

    /**
     * Build the evolution tree.
     *
     * This will create an array with the elements
     * "entity" => The Pokemon entity,
     * "active" => Is this Pokemon the active (source of the evolution tree request) Pokemon?
     * "children" => A list of evolution children.  Each item is the same format as this array.
     *
     * @param Pokemon $pokemon
     * @param Pokemon|null $activePokemon
     * @param bool $rootCall
     *
     * @return array
     */
    public function buildEvolutionTree(Pokemon $pokemon, ?Pokemon $activePokemon = null, bool $rootCall = true): array
    {
        if ($rootCall) {
            $activePokemon = $pokemon;
            $pokemon = $this->findEvolutionRoot($pokemon);
        }

        $tree = [
            'entity' => $pokemon,
            'active' => $pokemon === $activePokemon,
            'children' => [],
        ];

        $children = $this->getChildren($pokemon, true);
        foreach ($children as $child) {
            $tree['children'][] = $this->buildEvolutionTree($child, $activePokemon, false);
        }

        return $tree;
    }

    /**
     * @param Pokemon $pokemon
     *
     * @return Pokemon
     */
    public function findEvolutionRoot(Pokemon $pokemon): Pokemon
    {
        $qb = $this->getPathQueryBuilder($pokemon);
        $qb->setMaxResults(1);
        $q = $qb->getQuery();
        $q->execute();

        return $q->getOneOrNullResult();
    }
}
