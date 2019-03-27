<?php

namespace App\DataTable\Type;


use App\Entity\Pokemon;
use App\Entity\Version;
use Doctrine\ORM\QueryBuilder;
use Omines\DataTablesBundle\Adapter\Doctrine\ORMAdapter;
use Omines\DataTablesBundle\DataTable;

/**
 * Pokemon Table for Breeding Compatibility
 */
class BreedingPokemonTableType extends PokemonTableType
{

    /**
     * {@inheritdoc}
     */
    public function configure(DataTable $dataTable, array $options)
    {
        parent::configure($dataTable, $options);

        /** @var Version $version */
        $version = $options['version'];
        /** @var Pokemon $canBreedWithPokemon */
        $canBreedWithPokemon = $options['pokemon'];

        $dataTable->setName(self::class)->createAdapter(
            ORMAdapter::class,
            [
                'entity' => Pokemon::class,
                'query' => function (QueryBuilder $qb) use ($version, $canBreedWithPokemon) {
                    $this->query($qb, $version);
                    $qb->andWhere('pokemon != :canBreedWithPokemon')
                        ->andWhere('pokemon.mega = false')
                        ->setParameter('canBreedWithPokemon', $canBreedWithPokemon);
                    // Ditto can breed with any Pokemon but other Ditto, so get a list of
                    // all Pokemon that aren't Ditto.
                    if ($canBreedWithPokemon->getSlug() !== 'ditto') {
                        $qb->join('pokemon.eggGroups', 'egg_groups')
                            ->andWhere(
                            // Always include Ditto
                                $qb->expr()->orX(
                                    ':eggGroups MEMBER OF pokemon.eggGroups',
                                    "pokemon.slug = 'ditto'"
                                )
                            )->andWhere('pokemon.baby = false')
                            ->andWhere("egg_groups.slug != 'undiscovered'")
                            ->setParameter('eggGroups', $canBreedWithPokemon->getEggGroups());
                    }
                },
            ]
        );
    }

}
