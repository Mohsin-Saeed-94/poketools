<?php


namespace App\DataTable\Type;


use App\Entity\Shop;
use App\Entity\ShopItem;
use App\Entity\Version;
use Doctrine\ORM\QueryBuilder;
use Omines\DataTablesBundle\Adapter\Doctrine\ORMAdapter;
use Omines\DataTablesBundle\Column\TextColumn;
use Omines\DataTablesBundle\DataTable;

/**
 * Shop Inventory table
 */
class ShopItemTableType extends ItemTableType
{
    use ModifiedBaseTableTrait;

    /**
     * @inheritDoc
     */
    public function configure(DataTable $dataTable, array $options)
    {
        /** @var Version $version */
        $version = $options['version'];

        /** @var Shop $shop */
        $shop = $options['shop'];

        $heldItemColumns = $this->getColumnNames($dataTable);
        parent::configure($dataTable, $options);
        $this->fixPropertyPaths($dataTable, $heldItemColumns, 'item');

        $dataTable->add(
            'buy',
            TextColumn::class,
            [
                'label' => 'Price',
                'field' => 'shop_item.buy',
                'raw' => true,
                'render' => '<i class="pkt-icon pkt-icon-pokedollar"></i>%d',
            ]
        );

        $dataTable->setName(self::class.'__'.$shop->getLocationArea()->getTreePath().'__'.$shop->getSlug())
            ->createAdapter(
                ORMAdapter::class,
                [
                    'entity' => ShopItem::class,
                    'query' => function (QueryBuilder $qb) use ($version, $shop) {
                        $qb->select('shop_item')
                            ->from(ShopItem::class, 'shop_item')
                            ->join('shop_item.item', 'item');
                        $this->query($qb, $version);
                        $qb->andWhere('shop_item.shop = :shop')
                            ->setParameter('shop', $shop);
                    },
                ]
            );
    }

}
