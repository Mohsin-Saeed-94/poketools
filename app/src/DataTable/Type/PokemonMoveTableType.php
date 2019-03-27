<?php
/**
 * @file TypeMoveTableType.php
 */

namespace App\DataTable\Type;


use App\Entity\MoveInVersionGroup;
use App\Entity\MoveLearnMethod;
use App\Entity\Pokemon;
use App\Entity\PokemonMove;
use App\Entity\PokemonType;
use App\Entity\Type;
use App\Entity\Version;
use Doctrine\ORM\QueryBuilder;
use Omines\DataTablesBundle\Adapter\Doctrine\ORMAdapter;
use Omines\DataTablesBundle\Column\TextColumn;
use Omines\DataTablesBundle\DataTable;

/**
 * Move table for Pokemon view
 */
class PokemonMoveTableType extends MoveTableType
{
    use ModifiedBaseTableTrait;

    /**
     * {@inheritdoc}
     */
    public function configure(DataTable $dataTable, array $options)
    {
        /** @var Version $version */
        $version = $options['version'];
        /** @var Pokemon $pokemon */
        $pokemon = $options['pokemon'];
        /** @var MoveLearnMethod $learnMethod */
        $learnMethod = $options['learnMethod'];

        if ($learnMethod->getSlug() === 'level-up') {
            $dataTable->add(
                'level',
                TextColumn::class,
                [
                    'label' => 'Lv.',
                    'propertyPath' => 'level',
                    'className' => 'pkt-move-index-table-level',
                    'data' => function (PokemonMove $context, ?int $level) {
                        return $level ?? '–';
                    },
                ]
            );
        } elseif ($learnMethod->getSlug() === 'machine') {
            $dataTable->add(
                'machine',
                TextColumn::class,
                [
                    'label' => 'Machine',
                    'propertyPath' => 'machine.name',
                    'className' => 'pkt-move-index-table-machine',
                ]
            );
        }

        $pokemonMoveColumns = $this->getColumnNames($dataTable);
        parent::configure($dataTable, $options);
        $this->fixPropertyPaths($dataTable, $pokemonMoveColumns, 'move');

        // Create a hidden property to store if this move gets a STAB.
        $dataTable->add(
            'stab',
            TextColumn::class,
            [
                'visible' => false,
                'propertyPath' => 'move.type',
                'className' => 'pkt-move-index-table-stab',
                'data' => function (PokemonMove $context, Type $type) use ($pokemon) {
                    $pokemonTypes = $pokemon->getTypes()->map(
                        function (PokemonType $pokemonType) {
                            return $pokemonType->getType();
                        }
                    );
                    if ($pokemonTypes->contains($type)) {
                        return '1';
                    }

                    return '';
                },
            ]
        );
        if ($version->getVersionGroup()->hasFeatureString('move-damage-class')) {
            $moveDamageClass = true;
        } else {
            $moveDamageClass = false;
        }
        $dataTable->add(
            'same_damage_class',
            TextColumn::class,
            [
                'visible' => false,
                'propertyPath' => 'move',
                'className' => 'pkt-move-index-table-samedamageclass',
                'data' => function (PokemonMove $context, MoveInVersionGroup $move) use ($pokemon, $moveDamageClass) {
                    if ($moveDamageClass) {
                        $damageClass = $move->getDamageClass();
                    } else {
                        $damageClass = $move->getType()->getDamageClass();
                    }

                    $attackVal = $pokemon->getStatData('attack')->getBaseValue();
                    $specialAttackVal = $pokemon->getStatData('special-attack')->getBaseValue();
                    if ($attackVal > $specialAttackVal
                        && $damageClass->getSlug() === 'physical') {
                        return '1';
                    }
                    if ($attackVal < $specialAttackVal
                        && $damageClass->getSlug() === 'special') {
                        return '1';
                    }

                    return '';
                },
            ]
        );

        $dataTable->setName(self::class.'__'.$learnMethod->getSlug())->createAdapter(
            ORMAdapter::class,
            [
                'entity' => PokemonMove::class,
                'query' => function (QueryBuilder $qb) use ($version, $pokemon, $learnMethod) {
                    $qb->select('pokemon_move')
                        ->from(PokemonMove::class, 'pokemon_move')
                        ->join('pokemon_move.move', 'move');
                    $this->query($qb, $version);
                    $qb->andWhere('pokemon_move.learnMethod = :learnMethod')
                        ->andWhere('pokemon_move.pokemon = :pokemon')
                        ->setParameter('learnMethod', $learnMethod)
                        ->setParameter('pokemon', $pokemon);
                },
            ]
        );
    }

}
