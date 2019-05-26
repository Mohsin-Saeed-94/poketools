<?php

namespace App\Repository;

use App\Entity\Encounter;
use App\Entity\Pokemon;
use App\Entity\Version;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Encounter|null find($id, $lockMode = null, $lockVersion = null)
 * @method Encounter|null findOneBy(array $criteria, array $orderBy = null)
 * @method Encounter[]    findAll()
 * @method Encounter[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EncounterRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Encounter::class);
    }

    /**
     * Find encounters by Pokemon
     *
     * @param Pokemon $pokemon
     *
     * @param Version $version
     *
     * @return Encounter[]
     */
    public function findByPokemon(Pokemon $pokemon, Version $version): array
    {
        $qb = $this->createQueryBuilder('encounter');
        $qb->andWhere('encounter.version = :version')
            ->andWhere('encounter.pokemon = :pokemon')
            ->setParameter('version', $version)
            ->setParameter('pokemon', $pokemon);
        $q = $qb->getQuery();
        $q->execute();

        return $q->getResult();
    }
}
