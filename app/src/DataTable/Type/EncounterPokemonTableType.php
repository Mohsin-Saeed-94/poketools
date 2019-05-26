<?php

namespace App\DataTable\Type;


use App\Entity\Encounter;
use App\Entity\Version;
use App\Helpers\Labeler;
use App\Repository\PokemonRepository;
use Doctrine\ORM\QueryBuilder;
use League\CommonMark\CommonMarkConverter;
use Omines\DataTablesBundle\DataTable;

/**
 * Pokemon Table for encounters
 */
class EncounterPokemonTableType extends PokemonTableType
{
    use ModifiedBaseTableTrait;
    use EncounterTableTrait;

    /**
     * @var CommonMarkConverter
     */
    protected $markdown;

    /**
     * EncounterPokemonTableType constructor.
     *
     * @param Labeler $labeler
     * @param PokemonRepository $pokemonRepo
     * @param CommonMarkConverter $markdown
     */
    public function __construct(Labeler $labeler, PokemonRepository $pokemonRepo, CommonMarkConverter $markdown)
    {
        parent::__construct($labeler, $pokemonRepo);

        $this->markdown = $markdown;
    }

    /**
     * {@inheritdoc}
     */
    public function configure(DataTable $dataTable, array $options)
    {
        /** @var Version $version */
        $version = $options['version'];

        $this->addEncounterColumns($dataTable);

        // Apply some special mapping to the parent Pokemon columns so they can get
        // data from the correct place
        $encounterColumns = $this->getColumnNames($dataTable);
        parent::configure($dataTable, $options);
        $this->fixPropertyPaths($dataTable, $encounterColumns, 'pokemon');
    }

    /**
     * @param QueryBuilder $qb
     * @param Version $version
     */
    protected function query(QueryBuilder $qb, Version $version): void
    {
        if (!$qb->getDQLPart('from')) {
            $qb->from(Encounter::class, 'encounter');
        }
        $qb->distinct()->addSelect('encounter')
            ->join('encounter.pokemon', 'pokemon')
            ->addOrderBy('method.position')
            ->addOrderBy('encounter.position');
        parent::query($qb, $version);
        $qb->addSelect('method')
            ->join('encounter.method', 'method')
            ->andWhere('encounter.version = :version');
    }
}
