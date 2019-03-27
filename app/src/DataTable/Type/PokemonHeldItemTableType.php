<?php
/**
 * @file PokemonHeldItemTableType.php
 */

namespace App\DataTable\Type;


use App\Entity\Pokemon;
use App\Entity\PokemonWildHeldItem;
use App\Entity\Version;
use Doctrine\ORM\QueryBuilder;
use Omines\DataTablesBundle\Adapter\Doctrine\ORMAdapter;
use Omines\DataTablesBundle\Column\TextColumn;
use Omines\DataTablesBundle\DataTable;

/**
 * Class PokemonHeldItemTableType
 */
class PokemonHeldItemTableType extends ItemTableType
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

        $dataTable->add(
            'chance',
            TextColumn::class,
            [
                'label' => 'Chance',
                'field' => 'pokemon_wild_held_item.rate',
                'render' => '%u%%',
            ]
        );
        $heldItemColumns = $this->getColumnNames($dataTable);
        parent::configure($dataTable, $options);
        $this->fixPropertyPaths($dataTable, $heldItemColumns, 'item');

        $dataTable->setName(self::class)->createAdapter(
            ORMAdapter::class,
            [
                'entity' => PokemonWildHeldItem::class,
                'query' => function (QueryBuilder $qb) use ($version, $pokemon) {
                    $qb->select('pokemon_wild_held_item')
                        ->from(PokemonWildHeldItem::class, 'pokemon_wild_held_item')
                        ->join('pokemon_wild_held_item.item', 'item');
                    $this->query($qb, $version);
                    $qb->andWhere('pokemon_wild_held_item.pokemon = :pokemon')
                        ->andWhere('pokemon_wild_held_item.version = :version')
                        ->setParameter('pokemon', $pokemon)
                        ->setParameter('version', $version);
                },
            ]
        );
    }

}