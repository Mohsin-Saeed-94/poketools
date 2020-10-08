<?php

namespace App\Repository;

use App\Entity\ItemInVersionGroup;
use App\Entity\ShopItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ShopItem|null find($id, $lockMode = null, $lockVersion = null)
 * @method ShopItem|null findOneBy(array $criteria, array $orderBy = null)
 * @method ShopItem[]    findAll()
 * @method ShopItem[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ShopItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ShopItem::class);
    }

    /**
     * @param ItemInVersionGroup $item
     *
     * @return ShopItem[]
     */
    public function findByItem(ItemInVersionGroup $item): array
    {
        $qb = $this->createQueryBuilder('shop_item');
        $qb->where('shop_item.item = :item')
            ->setParameter('item', $item);
        $q = $qb->getQuery();
        $q->execute();

        return $q->getResult();
    }
}
