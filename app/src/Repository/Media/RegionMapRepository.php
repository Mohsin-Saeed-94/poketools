<?php

namespace App\Repository\Media;

use App\Entity\Media\RegionMap;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RegionMap|null find($id, $lockMode = null, $lockVersion = null)
 * @method RegionMap|null findOneBy(array $criteria, array $orderBy = null)
 * @method RegionMap[]    findAll()
 * @method RegionMap[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RegionMapRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RegionMap::class);
    }

    /**
     * Find using the region slug and map slug
     *
     * @param string $regionSlug
     * @param string $mapSlug
     *
     * @return RegionMap[]
     */
    public function findBySlugCombo(string $regionSlug, string $mapSlug)
    {
        $qb = $this->createQueryBuilder('region_map');
        $qb->join('region_map.region', 'region')
            ->andWhere('region.slug = :region')
            ->andWhere('region_map.slug = :map')
            ->setParameter('region', $regionSlug)
            ->setParameter('map', $mapSlug);
        $q = $qb->getQuery();
        $q->execute();

        return $q->getResult();
    }
}
