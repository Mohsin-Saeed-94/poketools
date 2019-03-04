<?php

namespace App\Repository;

use App\Entity\MoveInVersionGroup;
use App\Entity\MoveLearnMethod;
use App\Entity\PokemonMove;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method PokemonMove|null find($id, $lockMode = null, $lockVersion = null)
 * @method PokemonMove|null findOneBy(array $criteria, array $orderBy = null)
 * @method PokemonMove[]    findAll()
 * @method PokemonMove[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PokemonMoveRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, PokemonMove::class);
    }

//    /**
//     * @return PokemonMove[] Returns an array of PokemonMove objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?PokemonMove
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    /**
     * @param MoveInVersionGroup $move
     * @param MoveLearnMethod $learnMethod
     *
     * @return int
     */
    public function countByMoveAndLearnMethod(MoveInVersionGroup $move, MoveLearnMethod $learnMethod): int
    {
        $qb = $this->createQueryBuilder('pokemon_move');
        $qb->select('COUNT(pokemon_move)')
            ->where('pokemon_move.move = :move')
            ->andWhere('pokemon_move.learnMethod = :learnMethod')
            ->setParameter('move', $move)
            ->setParameter('learnMethod', $learnMethod);

        $q = $qb->getQuery();
        $q->execute();

        return $q->getSingleScalarResult();
    }
}
