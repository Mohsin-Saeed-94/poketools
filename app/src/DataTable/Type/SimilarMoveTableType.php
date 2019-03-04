<?php
/**
 * @file TypeMoveTableType.php
 */

namespace App\DataTable\Type;


use App\Entity\MoveInVersionGroup;
use App\Entity\Version;
use Doctrine\ORM\QueryBuilder;
use Omines\DataTablesBundle\Adapter\Doctrine\ORMAdapter;
use Omines\DataTablesBundle\DataTable;

/**
 * Move table for showing similar moves
 */
class SimilarMoveTableType extends MoveTableType
{
    /**
     * {@inheritdoc}
     */
    public function configure(DataTable $dataTable, array $options)
    {
        parent::configure($dataTable, $options);

        /** @var Version $version */
        $version = $options['version'];
        /** @var MoveInVersionGroup $move */
        $move = $options['move'];

        $dataTable->setName(self::class)->createAdapter(
            ORMAdapter::class,
            [
                'entity' => MoveInVersionGroup::class,
                'query' => function (QueryBuilder $qb) use ($version, $move) {
                    $this->query($qb, $version);
                    $qb->andWhere('move.effect = :moveEffect')
                        ->andWhere('move != :move')
                        ->setParameter('moveEffect', $move->getEffect())
                        ->setParameter('move', $move);
                },
            ]
        );
    }

}