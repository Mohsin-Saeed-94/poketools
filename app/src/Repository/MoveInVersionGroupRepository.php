<?php

namespace App\Repository;

use App\Entity\MoveInVersionGroup;
use App\Entity\Version;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method MoveInVersionGroup|null find($id, $lockMode = null, $lockVersion = null)
 * @method MoveInVersionGroup|null findOneBy(array $criteria, array $orderBy = null)
 * @method MoveInVersionGroup[]    findAll()
 * @method MoveInVersionGroup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MoveInVersionGroupRepository extends ServiceEntityRepository implements SlugAndVersionInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MoveInVersionGroup::class);
    }

//    /**
//     * @return MoveInVersionGroup[] Returns an array of MoveInVersionGroup objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?MoveInVersionGroup
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    /**
     * {@inheritdoc}
     */
    public function findOneByVersion(string $slug, Version $version): ?MoveInVersionGroup
    {
        $qb = $this->createQueryBuilder('move');
        $qb->join('move.versionGroup', 'version_group')
            ->andWhere('move.slug = :slug')
            ->andWhere(':version MEMBER OF version_group.versions')
            ->setParameter('slug', $slug)
            ->setParameter('version', $version);

        $q = $qb->getQuery();
        $q->execute();

        return $q->getOneOrNullResult();
    }
}
