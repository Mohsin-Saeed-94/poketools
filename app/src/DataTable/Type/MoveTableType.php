<?php

namespace App\DataTable\Type;


use App\DataTable\Column\LinkColumn;
use App\DataTable\Column\MarkdownColumn;
use App\Entity\MoveInVersionGroup;
use App\Entity\Version;
use Doctrine\ORM\QueryBuilder;
use Omines\DataTablesBundle\Adapter\Doctrine\ORMAdapter;
use Omines\DataTablesBundle\Column\TextColumn;
use Omines\DataTablesBundle\Column\TwigColumn;
use Omines\DataTablesBundle\DataTable;
use Omines\DataTablesBundle\DataTableTypeInterface;

/**
 * Move table
 */
class MoveTableType implements DataTableTypeInterface
{
    /**
     * @param DataTable $dataTable
     * @param array $options
     */
    public function configure(DataTable $dataTable, array $options)
    {
        /** @var Version $version */
        $version = $options['version'];

        $hasMoveDamageClass = $version->getVersionGroup()->hasFeatureString(
            'move-damage-class'
        );
        $dataTable->setName(self::class)->add(
            'name',
            LinkColumn::class,
            [
                'label' => 'Name',
                'route' => 'move_view',
                'routeParams' => [
                    'versionSlug' => $version->getSlug(),
                    'moveSlug' => function ($move) {
                        if (!is_a($move, MoveInVersionGroup::class) && method_exists($move, 'getMove')) {
                            $move = $move->getMove();
                        } else {
                            return null;
                        }

                        /** @var $move MoveInVersionGroup */
                        return $move->getSlug();
                    },
                ],
                'className' => 'pkt-move-index-table-name',
            ]
        )->add(
            'type',
            LinkColumn::class,
            [
                'label' => 'Type',
                'propertyPath' => 'type',
                'className' => 'pkt-move-index-table-type',
                'orderField' => 'type.position',
                'route' => 'type_view',
                'routeParams' => [
                    'versionSlug' => $version->getSlug(),
                    'typeSlug' => function ($move) {
                        if (!is_a($move, MoveInVersionGroup::class) && method_exists($move, 'getMove')) {
                            $move = $move->getMove();
                        } else {
                            return null;
                        }

                        /** @var $move MoveInVersionGroup */
                        return $move->getType()->getSlug();
                    },
                ],
                'linkClassName' => function ($move) {
                    if (!is_a($move, MoveInVersionGroup::class) && method_exists($move, 'getMove')) {
                        $move = $move->getMove();
                    } else {
                        return null;
                    }

                    /** @var $move MoveInVersionGroup */
                    return sprintf('pkt-type-emblem-%s', $move->getType()->getSlug());
                },
            ]
        )->add(
            'damageClass',
            TwigColumn::class,
            [
                'label' => 'Class',
                'propertyPath' => $hasMoveDamageClass ? 'damageClass' : 'type.damageClass',
                'className' => 'pkt-move-index-table-damageclass',
                'orderField' => $hasMoveDamageClass ? 'damage_class.position' : 'type_damage_class.position',
                'template' => '_data_table/damage_class.html.twig',
            ]
        )->add(
            'pp',
            TextColumn::class,
            [
                'label' => 'PP',
                'propertyPath' => 'pp',
                'className' => 'pkt-move-index-table-pp',
            ]
        )->add(
            'power',
            TextColumn::class,
            [
                'label' => 'Pwr.',
                'propertyPath' => 'power',
                'className' => 'pkt-move-index-table-power',
                'data' => function ($context, ?int $value) {
                    return $value ?? '–';
                },
            ]
        )->add(
            'accuracy',
            TextColumn::class,
            [
                'label' => 'Acc.',
                'propertyPath' => 'accuracy',
                'className' => 'pkt-move-index-table-accuracy',
                'data' => function ($context, ?int $value) {
                    if ($value === null) {
                        return '–';
                    }

                    return $value.'%';
                },
            ]
        )->add(
            'shortDescription',
            MarkdownColumn::class,
            [
                'label' => 'Description',
                'className' => 'pkt-move-index-table-description',
//                'field' => 'effect.shortDescription',
                'propertyPath' => 'effect.shortDescription',
                'orderable' => false,
                'render' => function (string $value, $move) {
                    if (!is_a($move, MoveInVersionGroup::class) && method_exists($move, 'getMove')) {
                        $move = $move->getMove();
                    } else {
                        return null;
                    }

                    /** @var $move MoveInVersionGroup */
                    if ($move->getEffectChance() !== null) {
                        return str_replace('$effect_chance', $move->getEffectChance(), $value);
                    }

                    return $value;
                },
            ]
        )->addOrderBy('name')->createAdapter(
            ORMAdapter::class,
            [
                'entity' => MoveInVersionGroup::class,
                'query' => function (QueryBuilder $qb) use ($version) {
                    $this->query($qb, $version);
                },
            ]
        );
    }

    /**
     * @param QueryBuilder $qb
     * @param Version $version
     */
    protected function query(QueryBuilder $qb, Version $version)
    {
        if (!$qb->getDQLPart('from')) {
            $qb->from(MoveInVersionGroup::class, 'move');
        }
        $qb->distinct()->addSelect('move')
            ->addSelect('effect')
            ->addSelect('damage_class')
            ->addSelect('type')
            ->join('move.versionGroup', 'version_group')
            ->join('move.effect', 'effect')
            ->leftJoin('move.damageClass', 'damage_class')
            ->join('move.type', 'type')
            ->leftJoin('type.damageClass', 'type_damage_class')
            ->andWhere(':version MEMBER OF version_group.versions')
            ->setParameter('version', $version);
    }
}
