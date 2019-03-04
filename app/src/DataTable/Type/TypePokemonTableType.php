<?php

namespace App\DataTable\Type;


use App\Entity\Pokemon;
use App\Entity\Type;
use App\Entity\Version;
use Doctrine\ORM\QueryBuilder;
use Omines\DataTablesBundle\Adapter\Doctrine\ORMAdapter;
use Omines\DataTablesBundle\DataTable;

/**
 * Pokemon Table for Type view
 */
class TypePokemonTableType extends PokemonTableType
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
                'entity' => Pokemon::class,
                'query' => function (QueryBuilder $qb) use ($version, $type) {
                    $this->query($qb, $version);
                    $qb->join('pokemon.types', 'pokemon_types')
                        ->join('pokemon_types.type', 'type')
                        ->andWhere(':type = type')
                        ->setParameter('type', $type);
                },
            ]
        );
    }

}
