<?php

namespace App\Repository;

use App\Entity\MoveDamageClass;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method MoveDamageClass|null find($id, $lockMode = null, $lockVersion = null)
 * @method MoveDamageClass|null findOneBy(array $criteria, array $orderBy = null)
 * @method MoveDamageClass[]    findAll()
 * @method MoveDamageClass[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MoveDamageClassRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, MoveDamageClass::class);
    }

//    /**
//     * @return MoveDamageClass[] Returns an array of MoveDamageClass objects
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
    public function findOneBySomeField($value): ?MoveDamageClass
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
