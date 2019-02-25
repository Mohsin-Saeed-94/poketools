<?php
/**
 * @file AbilityPokemonTableType.php
 */

namespace App\DataTable\Type;


use App\Entity\AbilityInVersionGroup;
use App\Entity\Version;
use App\Repository\PokemonRepository;
use Omines\DataTablesBundle\DataTable;

/**
 * Pokemon Table for Ability view
 */
class AbilityPokemonTableType extends PokemonTableType
{
    /**
     * @var PokemonRepository
     */
    private $pokemonRepo;

    /**
     * AbilityPokemonTableType constructor.
     *
     * @param PokemonRepository $pokemonRepo
     */
    public function __construct(PokemonRepository $pokemonRepo)
    {
        $this->pokemonRepo = $pokemonRepo;
    }

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

        $dataTable->getAdapter()->configure(
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
