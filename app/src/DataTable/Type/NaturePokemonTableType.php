<?php
/**
 * @file AbilityPokemonTableType.php
 */

namespace App\DataTable\Type;


use App\Entity\Nature;
use App\Entity\Version;
use App\Repository\PokemonRepository;
use Omines\DataTablesBundle\DataTable;

/**
 * Pokemon Table for Nature view
 */
class NaturePokemonTableType extends PokemonTableType
{
    /**
     * @var PokemonRepository
     */
    private $pokemonRepo;

    /**
     * NaturePokemonTableType constructor.
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
        /** @var Nature $nature */
        $nature = $options['nature'];

        $dataTable->getAdapter()->configure(
            [
                'data' => function (int $start, int $limit) use ($version, $nature) {
                    return $this->pokemonRepo->findMatchingStats(
                        $version,
                        $nature->getStatIncreased(),
                        $nature->getStatDecreased()
                    );
                },
            ]
        );
    }

}
