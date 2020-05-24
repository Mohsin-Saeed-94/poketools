<?php

namespace App\DataTable\Type;


use App\Entity\Pokemon;
use App\Entity\Version;
use Doctrine\ORM\QueryBuilder;
use Omines\DataTablesBundle\Adapter\Doctrine\ORMAdapter;
use Omines\DataTablesBundle\DataTable;

/**
 * Moves with flag
 */
class FlagMoveTableType extends MoveTableType
{
    /**
     * {@inheritdoc}
     */
    public function configure(DataTable $dataTable, array $options)
    {
        /** @var Version $version */
        $version = $options['version'];
        /** @var \App\Entity\MoveFlag $flag */
        $flag = $options['flag'];

        parent::configure($dataTable, $options);

        $dataTable->setName(self::class.'__'.$flag->getSlug())->createAdapter(
            ORMAdapter::class,
            [
                'entity' => Pokemon::class,
                'query' => function (QueryBuilder $qb) use ($version, $flag) {
                    $this->query($qb, $version);
                    $qb->andWhere(':flag MEMBER OF move.flags')
                        ->setParameter('flag', $flag);
                },
            ]
        );


    }

}
