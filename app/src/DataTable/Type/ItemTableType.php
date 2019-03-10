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

        /** @var ItemPocketInVersionGroup $pocket */
        $pocket = $options['pocket'];

        $dataTable->setName(self::class.'__'.$pocket->getSlug())->add(
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
                    'itemSlug' => function (ItemInVersionGroup $context, $value) {
                        return $context->getSlug();
                    },
                ],
                'className' => 'pkt-item-index-table-name',
                'render' => function ($value, ItemInVersionGroup $context) use ($version) {
                    return $this->labeler->item($context, $version);
                },
            ]
        )->add(
            'description',
            MarkdownColumn::class,
            [
                'label' => 'Description',
                'propertyPath' => 'shortDescription',
                'orderable' => false,
            ]
        )->createAdapter(
            ORMAdapter::class,
            [
                'entity' => ItemInVersionGroup::class,
                'query' => function (QueryBuilder $qb) use ($version, $pocket) {
                    $qb->select('item')
                        ->addSelect('category')
                        ->from(ItemInVersionGroup::class, 'item')
                        ->join('item.versionGroup', 'version_group')
                        ->join('item.category', 'category')
                        ->andWhere(':version MEMBER OF version_group.versions')
                        ->andWhere('item.pocket = :pocket')
                        ->addOrderBy('category.name')
                        ->setParameter('version', $version)
                        ->setParameter('pocket', $pocket);
                },
            ]
        )->addOrderBy('name');
    }
}
