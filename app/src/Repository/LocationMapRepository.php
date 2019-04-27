<?php

namespace App\Repository;

use App\Entity\LocationMap;
use App\Entity\Media\RegionMap;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method LocationMap|null find($id, $lockMode = null, $lockVersion = null)
 * @method LocationMap|null findOneBy(array $criteria, array $orderBy = null)
 * @method LocationMap[]    findAll()
 * @method LocationMap[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LocationMapRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, LocationMap::class);
    }

    /**
     * Find by region map
     *
     * @param RegionMap $map
     *
     * @return mixed
     */
    public function findByMap(RegionMap $map)
    {
        $qb = $this->createQueryBuilder('location_map');
        $qb->andWhere('location_map.map = :map')
            ->setParameter('map', $map);

        $q = $qb->getQuery();
        $q->execute();

        return $q->getResult();
    }
}
