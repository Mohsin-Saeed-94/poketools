<?php

namespace App\Repository;

use App\Entity\Pokemon;
use App\Entity\PokemonForm;
use App\Entity\Version;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method PokemonForm|null find($id, $lockMode = null, $lockVersion = null)
 * @method PokemonForm|null findOneBy(array $criteria, array $orderBy = null)
 * @method PokemonForm[]    findAll()
 * @method PokemonForm[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PokemonFormRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, PokemonForm::class);
    }

//    /**
//     * @return PokemonForm[] Returns an array of PokemonForm objects
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
    public function findOneBySomeField($value): ?PokemonForm
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
     * @param Pokemon $pokemon
     * @param Version $version
     * @param string|null $formSlug
     *   Pass null to find the default form for the pokemon.
     *
     * @return Pokemon|null
     */
    public function findOneByPokemon(Pokemon $pokemon, Version $version, ?string $formSlug): ?PokemonForm
    {
        $qb = $this->createQueryBuilder('form');
        $qb->join('form.pokemon', 'pokemon')
            ->join('pokemon.species', 'species')
            ->join('species.versionGroup', 'version_group')
            ->andWhere('form.pokemon = :pokemon')
            ->andWhere(':version MEMBER OF version_group.versions')
            ->setParameter('pokemon', $pokemon)
            ->setParameter('version', $version);

        if ($formSlug === null) {
            $qb->andWhere('form.isDefault = true');
        } else {
            $qb->andWhere('form.slug = :slug')
                ->setParameter('slug', $formSlug);
        }

        $q = $qb->getQuery();
        $q->execute();

        return $q->getOneOrNullResult();
    }
}
