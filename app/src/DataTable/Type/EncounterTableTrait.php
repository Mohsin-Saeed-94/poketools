<?php
/**
 * @file EncounterTableTrait.php
 */

namespace App\DataTable\Type;


use App\DataTable\Column\CollectionColumn;
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
            TwigColumn::class,
            [
                'label' => 'Chance',
                'field' => 'encounter.chance',
                'orderable' => false,
                'className' => 'pkt-encounter-table-chance',
                'template' => '_data_table/encounter_chance.html.twig',
                'render' => function (?int $value) {
                    if ($value === null) {
                        return 0;
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
                'childType' => TwigColumn::class,
                'childOptions' => [
                    'template' => '_data_table/encounter_condition.html.twig',
                ],
            ]
        )->add(
            'note',
            TextColumn::class,
            [
                'label' => 'Note',
                'field' => 'encounter.note',
                'orderable' => false,
                'visible' => false,
                'render' => function (?string $value) {
                    if ($value) {
                        return $this->markdown->convertToHtml($value);
                    }

                    return null;
                },
            ]
        );
    }
}
