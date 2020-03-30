<?php

namespace App\DataTable\Type;


use App\DataTable\Column\LinkColumn;
use App\DataTable\Column\MarkdownColumn;
use App\Entity\ItemInVersionGroup;
use App\Entity\ItemPocketInVersionGroup;
use App\Entity\Version;
use App\Helpers\Labeler;
use Doctrine\ORM\QueryBuilder;
use Omines\DataTablesBundle\Adapter\Doctrine\ORMAdapter;
use Omines\DataTablesBundle\Column\TextColumn;
use Omines\DataTablesBundle\DataTable;
use Omines\DataTablesBundle\DataTableTypeInterface;

/**
 * Item table
 */
class ItemTableType implements DataTableTypeInterface
{
    /**
     * @var Labeler
     */
    protected $labeler;

    /**
     * ItemTableType constructor.
     *
     * @param Labeler $labeler
     */
    public function __construct(Labeler $labeler)
    {
        $this->labeler = $labeler;
    }

    /**
     * @param DataTable $dataTable
     * @param array $options
     */
    public function configure(DataTable $dataTable, array $options)
    {
        /** @var Version $version */
        $version = $options['version'];

        /** @var ItemPocketInVersionGroup|null $pocket */
        $pocket = $options['pocket'] ?? null;

        $tableName = self::class;
        if ($pocket !== null) {
            $tableName .= '__'.$pocket->getSlug();
        }
        $dataTable->setName($tableName)->add(
            'category',
            TextColumn::class,
            [
                'label' => 'Category',
                'visible' => false,
                'propertyPath' => 'category.name',
            ]
        )->add(
            'name',
            LinkColumn::class,
            [
                'label' => 'Name',
                'route' => 'item_view',
                'routeParams' => [
                    'versionSlug' => $version->getSlug(),
                    'itemSlug' => function ($item, $value) {
                        // If this table is extended, the context can be something other than an Item.
                        /** @var ItemInVersionGroup $item */
                        if (!is_a($item, ItemInVersionGroup::class)) {
                            if (method_exists($item, 'getItem')) {
                                $item = $item->getItem();
                            } else {
                                return null;
                            }
                        }

                        return $item->getSlug();
                    },
                ],
                'className' => 'pkt-item-index-table-name',
                'render' => function ($value, $item) use ($version) {
                    // If this table is extended, the context can be something other than an Item.
                    /** @var ItemInVersionGroup $item */
                    if (!is_a($item, ItemInVersionGroup::class)) {
                        if (method_exists($item, 'getItem')) {
                            $item = $item->getItem();
                        } else {
                            return null;
                        }
                    }

                    return $this->labeler->item($item, $version);
                },
            ]
        )->add(
            'description',
            MarkdownColumn::class,
            [
                'label' => 'Description',
                'className' => 'pkt-text',
                'propertyPath' => 'shortDescription',
                'orderable' => false,
            ]
        )->createAdapter(
            ORMAdapter::class,
            [
                'entity' => ItemInVersionGroup::class,
                'query' => function (QueryBuilder $qb) use ($version, $pocket) {
                    $this->query($qb, $version, $pocket);
                },
            ]
        )->addOrderBy('name');
    }

    /**
     * @param QueryBuilder $qb
     * @param Version $version
     * @param ItemPocketInVersionGroup|null $pocket
     */
    protected function query(QueryBuilder $qb, Version $version, ?ItemPocketInVersionGroup $pocket = null): void
    {
        if (!$qb->getDQLPart('from')) {
            $qb->from(ItemInVersionGroup::class, 'item');
        }
        $qb->addSelect('item')
            ->addSelect('category')
            ->join('item.versionGroup', 'version_group')
            ->join('item.category', 'category')
            ->andWhere(':version MEMBER OF version_group.versions')
            ->addOrderBy('category.name')
            ->setParameter('version', $version);

        if ($pocket !== null) {
            $qb->andWhere('item.pocket = :pocket')
                ->setParameter('pocket', $pocket);
        }
    }
}
