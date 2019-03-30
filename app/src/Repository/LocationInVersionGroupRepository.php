<?php

namespace App\Repository;

use App\Entity\LocationInVersionGroup;
use App\Entity\RegionInVersionGroup;
use App\Entity\Version;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method LocationInVersionGroup|null find($id, $lockMode = null, $lockVersion = null)
 * @method LocationInVersionGroup|null findOneBy(array $criteria, array $orderBy = null)
 * @method LocationInVersionGroup[]    findAll()
 * @method LocationInVersionGroup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LocationInVersionGroupRepository extends ServiceEntityRepository implements SlugAndVersionInterface
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, LocationInVersionGroup::class);
    }

//    /**
//     * @return LocationInVersionGroup[] Returns an array of LocationInVersionGroup objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('l.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?LocationInVersionGroup
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    /**
     * @param RegionInVersionGroup $region
     *
     * @return LocationInVersionGroup[]
     */
    public function findByRegion(RegionInVersionGroup $region): array
    {
        $qb = $this->createQueryBuilder('location');
        $qb->andWhere('location.region = :region')
            ->setParameter('region', $region);

        $q = $qb->getQuery();
        $q->execute();

        return $q->getResult();
    }

    /**
     * {@inheritdoc}
     *
     * @return LocationInVersionGroup|null
     */
    public function findOneByVersion(string $slug, Version $version)
    {
        $qb = $this->createQueryBuilder('location');
        $qb->join('location.versionGroup', 'version_group')
            ->andWhere('location.slug = :slug')
            ->andWhere(':version MEMBER OF version_group.versions')
            ->setParameter('slug', $slug)
            ->setParameter('version', $version);

        $q = $qb->getQuery();
        $q->execute();

        return $q->getOneOrNullResult();
    }
}
