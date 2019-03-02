<?php

namespace App\DataTable\Type;


use App\DataTable\Adapter\ObjectAdapter;
use App\Entity\AbilityInVersionGroup;
use App\Entity\Version;
use Omines\DataTablesBundle\DataTable;

/**
 * Pokemon Table for Ability view
 */
class AbilityPokemonTableType extends PokemonTableType
{

    /**
     * {@inheritdoc}
     */
    public function configure(DataTable $dataTable, array $options)
    {
        parent::configure($dataTable, $options);

        /** @var Version $version */
        $version = $options['version'];
        /** @var AbilityInVersionGroup $ability */
        $ability = $options['ability'];

        $dataTable->setName(self::class)->createAdapter(
            ObjectAdapter::class,
            [
                'data' => function (int $start, int $limit) use ($ability) {
                    return $this->pokemonRepo->findWithAbility($ability, $start, $limit);
                },
                'count' => function () use ($ability) {
                    return $this->pokemonRepo->countWithAbility($ability);
                },
            ]
        );
    }

}
