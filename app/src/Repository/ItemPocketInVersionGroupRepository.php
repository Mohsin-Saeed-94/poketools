<?php

namespace App\Repository;

use App\Entity\ItemPocketInVersionGroup;
use App\Entity\Version;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ItemPocketInVersionGroup|null find($id, $lockMode = null, $lockVersion = null)
 * @method ItemPocketInVersionGroup|null findOneBy(array $criteria, array $orderBy = null)
 * @method ItemPocketInVersionGroup[]    findAll()
 * @method ItemPocketInVersionGroup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ItemPocketInVersionGroupRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ItemPocketInVersionGroup::class);
    }

//    /**
//     * @return ItemPocketInVersionGroup[] Returns an array of ItemPocketInVersionGroup objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('i.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ItemPocketInVersionGroup
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    /**
     * @param Version $version
     *
     * @return ItemPocketInVersionGroup[]
     */
    public function findByVersion(Version $version)
    {
        $qb = $this->createQueryBuilder('pocket');
        $qb->join('pocket.versionGroup', 'version_group')
            ->where(':version MEMBER OF version_group.versions')
            ->orderBy('pocket.position')
            ->setParameter('version', $version);

        $q = $qb->getQuery();
        $q->execute();

        return $q->getResult();
    }
}
