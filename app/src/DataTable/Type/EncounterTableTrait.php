<?php
/**
 * @file EncounterTableTrait.php
 */

namespace App\DataTable\Type;


use App\DataTable\Column\CollectionColumn;
use App\Entity\Encounter;
use Omines\DataTablesBundle\Column\TextColumn;
use Omines\DataTablesBundle\Column\TwigColumn;
use Omines\DataTablesBundle\DataTable;

/**
 * Handle common elements of encounter tables.
 */
trait EncounterTableTrait
{
    /**
     * @param DataTable $dataTable
     */
    protected function addEncounterColumns(DataTable $dataTable): void
    {
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
                'className' => 'pkt-encounter-table-chance',
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
                'className' => 'pkt-encounter-table-level',
            ]
        )->add(
            'conditions',
            CollectionColumn::class,
            [
                'label' => 'Conditions',
                'field' => 'encounter.conditions',
                'orderable' => false,
                'className' => 'pkt-encounter-table-conditions',
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
    }
}
