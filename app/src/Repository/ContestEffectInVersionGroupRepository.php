<?php

namespace App\Repository;

use App\Entity\ContestEffectInVersionGroup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method ContestEffectInVersionGroup|null find($id, $lockMode = null, $lockVersion = null)
 * @method ContestEffectInVersionGroup|null findOneBy(array $criteria, array $orderBy = null)
 * @method ContestEffectInVersionGroup[]    findAll()
 * @method ContestEffectInVersionGroup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ContestEffectInVersionGroupRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ContestEffectInVersionGroup::class);
    }
}
