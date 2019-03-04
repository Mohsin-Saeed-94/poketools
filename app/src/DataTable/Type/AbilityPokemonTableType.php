<?php

namespace App\DataTable\Type;


use App\Entity\AbilityInVersionGroup;
use App\Entity\Pokemon;
use App\Entity\Version;
use Doctrine\ORM\QueryBuilder;
use Omines\DataTablesBundle\Adapter\Doctrine\ORMAdapter;
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
            ORMAdapter::class,
            [
                'entity' => Pokemon::class,
                'query' => function (QueryBuilder $qb) use ($version, $ability) {
                    $this->query($qb, $version);
                    $qb->join('pokemon.abilities', 'pokemon_abilities')
                        ->andWhere('pokemon_abilities.ability = :ability')
                        ->setParameter('ability', $ability);
                },
            ]
        );
    }

}
