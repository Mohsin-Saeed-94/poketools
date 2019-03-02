<?php

namespace App\DataTable\Type;


use App\DataTable\Column\LinkColumn;
use App\DataTable\Column\MarkdownColumn;
use App\Entity\ContestType;
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
                'uri' => '#',
                'className' => 'pkt-move-index-table-name',
            ]
        )->add(
            'type',
            LinkColumn::class,
            [
                'label' => 'Type',
                'propertyPath' => 'type',
                'orderField' => 'move.type.position',
                'route' => 'type_view',
                'routeParams' => [
                    'versionSlug' => $version->getSlug(),
                    'typeSlug' => function (MoveInVersionGroup $context) {
                        return $context->getType()->getSlug();
                    },
                ],
                'linkClassName' => function (MoveInVersionGroup $context) {
                    return sprintf('pkt-type-emblem-%s', $context->getType()->getSlug());
                },
            ]
        );
        if ($version->getVersionGroup()->hasFeatureString('contests')
            || $version->getVersionGroup()->hasFeatureString('super-contests')) {
            $dataTable->add(
                'contestType',
                LinkColumn::class,
                [
                    'label' => 'Contest',
                    'propertyPath' => 'contestType',
                    // @todo Type link
                    'uri' => '#',
                    'orderField' => 'contest_type.position',
                    'data' => function (MoveInVersionGroup $context, ContestType $value) {
                        return $value->getName();
                    },
                    'linkClassName' => function (MoveInVersionGroup $context, string $value) {
                        return sprintf('pkt-type-emblem-%s', $context->getContestType()->getSlug());
                    },
                ]
            );
        }
        $dataTable->add(
            'damageClass',
            TwigColumn::class,
            [
                'label' => 'Class',
                'propertyPath' => $hasMoveDamageClass ? 'damageClass' : 'type.damageClass',
                'orderField' => $hasMoveDamageClass ? 'damage_class.position' : 'type_damage_class.position',
                'template' => '_data_table/damage_class.html.twig',
            ]
        )->add(
            'pp',
            TextColumn::class,
            [
                'label' => 'PP',
            ]
        )->add(
            'power',
            TextColumn::class,
            [
                'label' => 'Pwr.',
                'data' => function ($context, ?int $value) {
                    return $value ?? '–';
                },
            ]
        )->add(
            'accuracy',
            TextColumn::class,
            [
                'label' => 'Acc.',
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
                'field' => 'effect.shortDescription',
                'orderable' => false,
                'render' => function (string $value, MoveInVersionGroup $context) {
                    if ($context->getEffectChance() !== null) {
                        return str_replace('$effect_chance', $context->getEffectChance(), $value);
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
        $qb->select('move')
            ->addSelect('effect')
            ->addSelect('damage_class')
            ->addSelect('type')
            ->addSelect('contest_type')
            ->from(MoveInVersionGroup::class, 'move')
            ->join('move.versionGroup', 'version_group')
            ->join('move.effect', 'effect')
            ->leftJoin('move.damageClass', 'damage_class')
            ->join('move.type', 'type')
            ->leftJoin('type.damageClass', 'type_damage_class')
            ->leftJoin('move.contestType', 'contest_type')
            ->andWhere(':version MEMBER OF version_group.versions')
            ->setParameter('version', $version);
    }
}
