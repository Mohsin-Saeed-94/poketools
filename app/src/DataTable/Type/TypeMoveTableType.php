<?php
/**
 * @file TypeMoveTableType.php
 */

namespace App\DataTable\Type;


use App\Entity\MoveInVersionGroup;
use App\Entity\Type;
use App\Entity\Version;
use Doctrine\ORM\QueryBuilder;
use Omines\DataTablesBundle\Adapter\Doctrine\ORMAdapter;
use Omines\DataTablesBundle\DataTable;

/**
 * Move table for Type view
 */
class TypeMoveTableType extends MoveTableType
{
    /**
     * {@inheritdoc}
     */
    public function configure(DataTable $dataTable, array $options)
    {
        parent::configure($dataTable, $options);

        /** @var Version $version */
        $version = $options['version'];
        /** @var Type $type */
        $type = $options['type'];

        $dataTable->setName(self::class)->createAdapter(
            ORMAdapter::class,
            [
                'entity' => MoveInVersionGroup::class,
                'query' => function (QueryBuilder $qb) use ($version, $type) {
                    $this->query($qb, $version);
                    $qb->andWhere('move.type = :type')
                        ->setParameter('type', $type);
                },
            ]
        );
    }

}