<?php

namespace App\Repository;

use App\Entity\AbilityInVersionGroup;
use App\Entity\Version;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method AbilityInVersionGroup|null find($id, $lockMode = null, $lockVersion = null)
 * @method AbilityInVersionGroup|null findOneBy(array $criteria, array $orderBy = null)
 * @method AbilityInVersionGroup[]    findAll()
 * @method AbilityInVersionGroup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AbilityInVersionGroupRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, AbilityInVersionGroup::class);
    }

//    /**
//     * @return AbilityInVersionGroup[] Returns an array of AbilityInVersionGroup objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?AbilityInVersionGroup
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    /**
     * @param string $slug
     * @param Version $version
     *
     * @return AbilityInVersionGroup|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneByVersion(string $slug, Version $version): ?AbilityInVersionGroup
    {
        $qb = $this->createQueryBuilder('ability_in_version_group');
        $qb->join('ability_in_version_group.versionGroup', 'version_group')
            ->andWhere('ability_in_version_group.slug = :slug')
            ->andWhere(':version MEMBER OF version_group.versions');
        $q = $qb->getQuery();
        $q->execute(
            [
                'slug' => $slug,
                'version' => $version,
            ]
        );

        return $q->getOneOrNullResult();
    }
}
