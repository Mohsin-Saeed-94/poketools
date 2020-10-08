<?php

namespace App\Repository;

use App\Entity\ContestEffect;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ContestEffect|null find($id, $lockMode = null, $lockVersion = null)
 * @method ContestEffect|null findOneBy(array $criteria, array $orderBy = null)
 * @method ContestEffect[]    findAll()
 * @method ContestEffect[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ContestEffectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ContestEffect::class);
    }
}
