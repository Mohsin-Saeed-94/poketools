<?php

namespace App\DataTable\Type;


use App\DataTable\Column\CollectionColumn;
use App\Entity\Encounter;
use App\Entity\LocationArea;
use App\Entity\Version;
use App\Helpers\Labeler;
use App\Repository\PokemonRepository;
use Doctrine\ORM\QueryBuilder;
use League\CommonMark\CommonMarkConverter;
use Omines\DataTablesBundle\Adapter\Doctrine\ORMAdapter;
use Omines\DataTablesBundle\Column\TextColumn;
use Omines\DataTablesBundle\Column\TwigColumn;
use Omines\DataTablesBundle\DataTable;

/**
 * Pokemon Table for encounters
 */
class EncounterPokemonTableType extends PokemonTableType
{
    use ModifiedBaseTableTrait;

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
        /** @var LocationArea $locationArea */
        $locationArea = $options['location_area'];

        $dataTable->add(
            'method',
            TextColumn::class,
            [
                'label' => 'Method',
                'field' => 'encounter.method',
                'orderable' => false,
                'visible' => false,
            ]
        )->add(
            'chance',
            // @todo make this a gauge
            TextColumn::class,
            [
                'label' => 'Chance',
                'field' => 'encounter.chance',
                'orderable' => false,
                'className' => 'pkt-location-view-encounter-table-chance',
                'render' => function (?int $value) {
                    if ($value === null) {
                        return '';
                    }

                    return $value.'%';
                },
            ]
        )->add(
            'level',
            TextColumn::class,
            [
                'label' => 'Lvl',
                'field' => 'encounter.level',
                'orderable' => false,
                'className' => 'pkt-location-view-encounter-table-level',
            ]
        )->add(
            'conditions',
            CollectionColumn::class,
            [
                'label' => 'Conditions',
                'field' => 'encounter.conditions',
                'orderable' => false,
                'className' => 'pkt-location-view-encounter-table-conditions',
                'render' => function (array $conditions, Encounter $encounter) {
                    if ($encounter->getNote()) {
                        $conditions[] = sprintf(
                            '<a href="#" data-toggle="tooltip" data-html="true" title="%s">Special note!</a>',
                            $this->markdown->convertToHtml($encounter->getNote())
                        );
                    }

                    return $conditions;
                },
                'childType' => TwigColumn::class,
                'childOptions' => [
                    'template' => '_data_table/encounter_condition.html.twig',
                ],
            ]
        );

        // Apply some special mapping to the parent Pokemon columns so they can get
        // data from the correct place
        $encounterColumns = $this->getColumnNames($dataTable);
        parent::configure($dataTable, $options);
        $this->fixPropertyPaths($dataTable, $encounterColumns, 'pokemon');

        $dataTable->setName(self::class.'__'.$locationArea->getSlug())->createAdapter(
            ORMAdapter::class,
            [
                'entity' => Encounter::class,
                'query' => function (QueryBuilder $qb) use ($version, $locationArea) {
                    $qb->select('encounter')
                        ->from(Encounter::class, 'encounter')
                        ->join('encounter.pokemon', 'pokemon')
                        ->addOrderBy('method.position')
                        ->addOrderBy('encounter.position');
                    $this->query($qb, $version);
                    $qb->addSelect('method')
                        ->join('encounter.method', 'method')
                        ->andWhere('encounter.locationArea = :locationArea')
                        ->andWhere('encounter.version = :version')
                        ->setParameter('locationArea', $locationArea);
                },
            ]
        );
    }
}
