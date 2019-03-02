<?php

namespace App\DataTable\Type;


use App\DataTable\Adapter\ObjectAdapter;
use App\Entity\Type;
use App\Entity\Version;
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
            ObjectAdapter::class,
            [
                'data' => function (int $start, int $limit) use ($version, $type) {
                    return $this->pokemonRepo->findWithType($version, $type, $start, $limit);
                },
                'count' => function () use ($version, $type) {
                    return $this->pokemonRepo->countWithType($version, $type);
                },
            ]
        );
    }

}
