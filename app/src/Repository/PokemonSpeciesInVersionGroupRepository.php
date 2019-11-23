<?php

namespace App\Repository;

use App\Entity\PokemonSpeciesInVersionGroup;
use App\Entity\Version;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method PokemonSpeciesInVersionGroup|null find($id, $lockMode = null, $lockVersion = null)
 * @method PokemonSpeciesInVersionGroup|null findOneBy(array $criteria, array $orderBy = null)
 * @method PokemonSpeciesInVersionGroup[]    findAll()
 * @method PokemonSpeciesInVersionGroup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PokemonSpeciesInVersionGroupRepository extends ServiceEntityRepository implements SlugAndVersionInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PokemonSpeciesInVersionGroup::class);
    }

//    /**
//     * @return PokemonSpeciesInVersionGroup[] Returns an array of PokemonSpeciesInVersionGroup objects
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
    public function findOneBySomeField($value): ?PokemonSpeciesInVersionGroup
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
    public function findOneByVersion(string $slug, Version $version): ?PokemonSpeciesInVersionGroup
    {
        $qb = $this->createQueryBuilder('species');
        $qb->join('species.versionGroup', 'version_group')
            ->andWhere('species.slug = :slug')
            ->andWhere(':version MEMBER OF version_group.versions')
            ->setParameter('slug', $slug)
            ->setParameter('version', $version);
        $q = $qb->getQuery();
        $q->execute();

        return $q->getOneOrNullResult();
    }
}
