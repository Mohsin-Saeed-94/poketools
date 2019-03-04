<?php

namespace App\Repository;

use App\Entity\Type;
use App\Entity\TypeChart;
use App\Entity\Version;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method TypeChart|null find($id, $lockMode = null, $lockVersion = null)
 * @method TypeChart|null findOneBy(array $criteria, array $orderBy = null)
 * @method TypeChart[]    findAll()
 * @method TypeChart[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TypeChartRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, TypeChart::class);
    }

//    /**
//     * @return TypeChart[] Returns an array of TypeChart objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?TypeChart
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    /**
     * Find a type in a given type chart.
     *
     * @param string $typeSlug
     * @param Version $version
     *
     * @return Type|null
     */
    public function findTypeInTypeChart(string $typeSlug, Version $version): ?Type
    {
        // Find the applicable type chart.
        $typeChart = $this->findOneByVersion($version);
        if ($typeChart === null) {
            return null;
        }

        $type = null;
        foreach ($typeChart->getTypes() as $testType) {
            if ($testType->getSlug() === $typeSlug) {
                $type = $testType;
                break;
            }
        }

        return $type;
    }

    /**
     * @param Version $version
     *
     * @return TypeChart|null
     */
    public function findOneByVersion(Version $version): ?TypeChart
    {
        $qb = $this->createQueryBuilder('type_chart');
        $qb->join('type_chart.versionGroups', 'version_groups')
            ->andWhere(':version MEMBER OF version_groups.versions')
            ->setMaxResults(1)
            ->setParameter('version', $version);

        $q = $qb->getQuery();
        $q->execute();

        return $q->getOneOrNullResult();
    }
}
