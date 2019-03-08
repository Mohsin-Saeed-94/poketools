<?php

namespace App\Repository;

use App\Entity\ItemInVersionGroup;
use App\Entity\Version;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ItemInVersionGroup|null find($id, $lockMode = null, $lockVersion = null)
 * @method ItemInVersionGroup|null findOneBy(array $criteria, array $orderBy = null)
 * @method ItemInVersionGroup[]    findAll()
 * @method ItemInVersionGroup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ItemInVersionGroupRepository extends ServiceEntityRepository implements SlugAndVersionInterface
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ItemInVersionGroup::class);
    }

//    /**
//     * @return ItemInVersionGroup[] Returns an array of ItemInVersionGroup objects
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
    public function findOneBySomeField($value): ?ItemInVersionGroup
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
     * {@inheritdoc}
     */
    public function findOneByVersion(string $slug, Version $version): ?ItemInVersionGroup
    {
        $qb = $this->createQueryBuilder('item');
        $qb->join('item.versionGroup', 'version_group')
            ->where(':version MEMBER OF version_group.versions')
            ->andWhere('item.slug = :slug')
            ->setParameter('version', $version)
            ->setParameter('slug', $slug);

        $q = $qb->getQuery();
        $q->execute();

        return $q->getOneOrNullResult();
    }
}
