<?php

namespace App\Repository;

use App\Entity\LocationInVersionGroup;
use App\Entity\RegionInVersionGroup;
use App\Entity\Version;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Tree\Entity\Repository\MaterializedPathRepository;
use LogicException;

/**
 * @method LocationInVersionGroup|null find($id, $lockMode = null, $lockVersion = null)
 * @method LocationInVersionGroup|null findOneBy(array $criteria, array $orderBy = null)
 * @method LocationInVersionGroup[]    findAll()
 * @method LocationInVersionGroup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LocationInVersionGroupRepository extends MaterializedPathRepository implements ServiceEntityRepositoryInterface, SlugAndVersionInterface
{
    /**
     * LocationInVersionGroupRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        $entityClass = LocationInVersionGroup::class;
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
