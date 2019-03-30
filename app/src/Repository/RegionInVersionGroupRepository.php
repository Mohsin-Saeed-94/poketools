<?php

namespace App\Repository;

use App\Entity\RegionInVersionGroup;
use App\Entity\Version;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method RegionInVersionGroup|null find($id, $lockMode = null, $lockVersion = null)
 * @method RegionInVersionGroup|null findOneBy(array $criteria, array $orderBy = null)
 * @method RegionInVersionGroup[]    findAll()
 * @method RegionInVersionGroup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RegionInVersionGroupRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, RegionInVersionGroup::class);
    }

    // /**
    //  * @return RegionInVersionGroup[] Returns an array of RegionInVersionGroup objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?RegionInVersionGroup
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    /**
     * @param Version $version
     *
     * @return RegionInVersionGroup[]
     */
    public function findByVersion(Version $version)
    {
        $qb = $this->createQueryBuilder('region');
        $qb->join('region.versionGroup', 'version_group')
            ->andWhere(':version MEMBER OF version_group.versions')
            ->setParameter('version', $version);
        $q = $qb->getQuery();
        $q->execute();

        return $q->getResult();
    }
}
