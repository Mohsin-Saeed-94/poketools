<?php

namespace App\Repository;

use App\Entity\Version;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Version|null find($id, $lockMode = null, $lockVersion = null)
 * @method Version|null findOneBy(array $criteria, array $orderBy = null)
 * @method Version[]    findAll()
 * @method Version[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VersionRepository extends ServiceEntityRepository
{
    /**
     * @var string
     */
    private $defaultVersionSlug;

    /**
     * VersionRepository constructor.
     *
     * @param RegistryInterface $registry
     * @param string $defaultVersionSlug
     */
    public function __construct(RegistryInterface $registry, string $defaultVersionSlug)
    {
        parent::__construct($registry, Version::class);

        $this->defaultVersionSlug = $defaultVersionSlug;
    }

//    /**
//     * @return Version[] Returns an array of Version objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('v.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Version
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    /**
     * Get a list of all versions grouped by generation.
     *
     * The result is a multi-dimensional array.  The first level is keyed by
     * generation name, the second level is a list of versions in that generation.
     *
     * @return array
     */
    public function findAllVersionsGroupedByGeneration(): array
    {
        $qb = $this->createQueryBuilder('version');
        $qb->addSelect('version_group')->addSelect('generation')
            ->join('version.versionGroup', 'version_group')
            ->join('version_group.generation', 'generation')
            ->orderBy('version.position');
        $q = $qb->getQuery();
        /** @var Version[] $results */
        $results = $q->execute();

        $groupedResults = [];
        foreach ($results as $version) {
            $generation = $version->getVersionGroup()->getGeneration();
            $groupedResults[$generation->getName()][] = $version;
        }

        return $groupedResults;
    }

    /**
     * @return Version
     */
    public function getDefaultVersion(): Version
    {
        return $this->findOneBy(['slug' => $this->defaultVersionSlug]);
    }
}
