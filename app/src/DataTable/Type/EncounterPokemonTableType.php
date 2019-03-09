<?php

namespace App\DataTable\Type;


use App\DataTable\Column\CollectionColumn;
use App\Entity\Encounter;
use App\Entity\LocationArea;
use App\Entity\Version;
use App\Repository\PokemonRepository;
use Doctrine\ORM\QueryBuilder;
use League\CommonMark\CommonMarkConverter;
use Omines\DataTablesBundle\Adapter\Doctrine\ORMAdapter;
use Omines\DataTablesBundle\Column\AbstractColumn;
use Omines\DataTablesBundle\Column\TextColumn;
use Omines\DataTablesBundle\DataTable;

/**
 * Pokemon Table for encounters
 */
class EncounterPokemonTableType extends PokemonTableType
{
    /**
     * @var CommonMarkConverter
     */
    protected $markdown;

    public function __construct(PokemonRepository $pokemonRepo, CommonMarkConverter $markdown)
    {
        parent::__construct($pokemonRepo);

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
                // @todo make these icons
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
                'childType' => TextColumn::class,
            ]
        );

        // Apply some special mapping to the parent Pokemon columns so they can get
        // data from the correct place
        $encounterColumns = array_map([$this, 'columnName'], $dataTable->getColumns());
        parent::configure($dataTable, $options);
        $allColumns = array_map([$this, 'columnName'], $dataTable->getColumns());
        $pokemonColumns = array_diff($allColumns, $encounterColumns);
        foreach ($pokemonColumns as $columnName) {
            $column = $dataTable->getColumnByName($columnName);
            $columnPropertyPath = $column->getPropertyPath();
            if ($columnPropertyPath !== null) {
                $columnPropertyPath = 'pokemon.'.$columnPropertyPath;
            } else {
                $columnPropertyPath = 'pokemon';
            }
            $column->setOption('propertyPath', $columnPropertyPath);
        }

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

    /**
     * Callback used to filter columns.
     *
     * @param AbstractColumn $column
     *
     * @return string
     */
    protected function columnName(AbstractColumn $column): string
    {
        return $column->getName();
    }

}
