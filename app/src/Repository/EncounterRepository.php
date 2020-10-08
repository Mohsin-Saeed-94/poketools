<?php

namespace App\Repository;

use App\Entity\Encounter;
use App\Entity\LocationArea;
use App\Entity\Pokemon;
use App\Entity\Version;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Encounter|null find($id, $lockMode = null, $lockVersion = null)
 * @method Encounter|null findOneBy(array $criteria, array $orderBy = null)
 * @method Encounter[]    findAll()
 * @method Encounter[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EncounterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
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
        $qb->join('encounter.locationArea', 'location_area')
            ->join('location_area.location', 'location')
            ->join('encounter.method', 'method')
            ->andWhere('encounter.version = :version')
            ->andWhere('encounter.pokemon = :pokemon')
            ->addOrderBy('location.name')
            ->addOrderBy('location_area.position')
            ->addOrderBy('method.position')
            ->addOrderBy('encounter.chance', 'DESC')
            ->setParameter('version', $version)
            ->setParameter('pokemon', $pokemon);
        $q = $qb->getQuery();
        // These associations will definitely be needed for sorting, below.
        $q->setFetchMode(Encounter::class, 'locationArea', ClassMetadataInfo::FETCH_EAGER)
            ->setFetchMode(LocationArea::class, 'location', ClassMetadataInfo::FETCH_EAGER)
            ->setFetchMode(Encounter::class, 'method', ClassMetadataInfo::FETCH_EAGER);
        $q->execute();

        $result = $q->getResult();

        // Need to natural sort everything
        uasort(
            $result,
            function (Encounter $a, Encounter $b) {
                $aLocationArea = $a->getLocationArea();
                $bLocationArea = $b->getLocationArea();
                $aLocation = $aLocationArea->getLocation();
                $bLocation = $bLocationArea->getLocation();
                if ($aLocation->getId() !== $bLocation->getId()) {
                    return strnatcmp($aLocation->getName(), $bLocation->getName());
                }

                if ($aLocationArea->getId() !== $bLocationArea->getId()) {
                    return $aLocationArea->getPosition() - $bLocationArea->getPosition();
                }

                $aMethod = $a->getMethod();
                $bMethod = $b->getMethod();
                if ($aMethod->getId() !== $bMethod->getId()) {
                    return $aMethod->getPosition() - $bMethod->getPosition();
                }

                return $b->getChance() - $a->getChance();
            }
        );

        return $result;
    }

    /**
     * Count the encounters that occur in an area
     *
     * @param LocationArea $area
     * @param Version $version
     *
     * @return int
     */
    public function countInArea(LocationArea $area, Version $version): int
    {
        $qb = $this->createQueryBuilder('encounter');
        $qb->select('COUNT(encounter.id)')
            ->andWhere('encounter.version = :version')
            ->andWhere('encounter.locationArea = :area')
            ->setParameter('version', $version)
            ->setParameter('area', $area);
        $q = $qb->getQuery();
        $q->execute();

        return $q->getSingleScalarResult();
    }
}
