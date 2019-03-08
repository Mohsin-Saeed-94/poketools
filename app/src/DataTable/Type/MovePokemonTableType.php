<?php

namespace App\DataTable\Type;


use App\Entity\MoveInVersionGroup;
use App\Entity\MoveLearnMethod;
use App\Entity\Pokemon;
use App\Entity\PokemonMove;
use App\Entity\Version;
use Doctrine\ORM\QueryBuilder;
use Omines\DataTablesBundle\Adapter\Doctrine\ORMAdapter;
use Omines\DataTablesBundle\Column\TextColumn;
use Omines\DataTablesBundle\DataTable;

/**
 * Pokemon Table for Move view
 */
class MovePokemonTableType extends PokemonTableType
{
    /**
     * {@inheritdoc}
     */
    public function configure(DataTable $dataTable, array $options)
    {
        /** @var Version $version */
        $version = $options['version'];
        /** @var MoveInVersionGroup $move */
        $move = $options['move'];
        /** @var MoveLearnMethod $learnMethod */
        $learnMethod = $options['learnMethod'];

        if ($learnMethod->getSlug() === 'level-up') {
            $dataTable->add(
                'level',
                TextColumn::class,
                [
                    'label' => 'Lv.',
                    'propertyPath' => 'moves',
                    'data' => function (Pokemon $pokemon) use ($move, $learnMethod) {
                        /** @var PokemonMove $pokemonMove */
                        $pokemonMove = $pokemon->getMoves()->filter(
                            function (PokemonMove $test) use ($move, $learnMethod) {
                                return $test->getMove() === $move && $test->getLearnMethod() === $learnMethod;
                            }
                        )->first();

                        return $pokemonMove->getLevel() ?? '–';
                    },
                ]
            );
        } elseif ($learnMethod->getSlug() === 'machine') {
            $dataTable->add(
                'machine',
                TextColumn::class,
                [
                    'label' => 'Machine',
                    'propertyPath' => 'moves',
                    'data' => function (Pokemon $pokemon) use ($move, $learnMethod) {
                        /** @var PokemonMove $pokemonMove */
                        $pokemonMove = $pokemon->getMoves()->filter(
                            function (PokemonMove $test) use ($move, $learnMethod) {
                                return $test->getMove() === $move && $test->getLearnMethod() === $learnMethod;
                            }
                        )->first();

                        return $pokemonMove->getMachine();
                    },
                ]
            );
        }

        parent::configure($dataTable, $options);

        $dataTable->setName(self::class.'__'.$learnMethod->getSlug())->createAdapter(
            ORMAdapter::class,
            [
                'entity' => Pokemon::class,
                'query' => function (QueryBuilder $qb) use ($version, $move, $learnMethod) {
                    $this->query($qb, $version);
                    $qb->join('pokemon.moves', 'pokemon_move')
                        ->andWhere('pokemon_move.move = :move')
                        ->andWhere('pokemon_move.learnMethod = :learnMethod')
                        ->setParameter('move', $move)
                        ->setParameter('learnMethod', $learnMethod);
                },
            ]
        );


    }

}
