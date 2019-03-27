<?php

namespace App\Repository;

use App\Entity\MoveLearnMethod;
use App\Entity\Pokemon;
use App\Entity\PokemonMove;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method MoveLearnMethod|null find($id, $lockMode = null, $lockVersion = null)
 * @method MoveLearnMethod|null findOneBy(array $criteria, array $orderBy = null)
 * @method MoveLearnMethod[]    findAll()
 * @method MoveLearnMethod[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MoveLearnMethodRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, MoveLearnMethod::class);
    }

//    /**
//     * @return MoveLearnMethod[] Returns an array of MoveLearnMethod objects
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
    public function findOneBySomeField($value): ?MoveLearnMethod
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
     * @param Pokemon $pokemon
     *
     * @return MoveLearnMethod[]
     */
    public function findUsedMethodsForPokemon(Pokemon $pokemon): array
    {
        $qb = $this->createQueryBuilder('learn_method');
        $qb->join(PokemonMove::class, 'pokemon_move', 'WITH', 'pokemon_move.learnMethod = learn_method')
            ->andWhere('pokemon_move.pokemon = :pokemon')
            ->setParameter('pokemon', $pokemon);
        $q = $qb->getQuery();
        $q->execute();

        return $q->getResult();
    }
}
