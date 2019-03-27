<?php
/**
 * @file ModifiedBaseTableTrait.php
 */

namespace App\DataTable\Type;


use Omines\DataTablesBundle\Column\AbstractColumn;
use Omines\DataTablesBundle\DataTable;


/**
 * Helpers to use when modifying a parent table class using a different base table.
 */
trait ModifiedBaseTableTrait
{
    /**
     * Modify property paths in columns inherited from the parent table class to refer to the correct
     * properties.
     *
     * @param DataTable $dataTable
     * @param string[] $addedColumns
     *   A list of column names that should not have their property paths modified.
     * @param string $propertyPathPrefix
     *   The property to prefix all property paths with.
     */
    protected function fixPropertyPaths(DataTable $dataTable, array $addedColumns, string $propertyPathPrefix): void
    {
        $allColumns = $this->getColumnNames($dataTable);
        $inheritedColumns = array_diff($allColumns, $addedColumns);
        foreach ($inheritedColumns as $inheritedColumnName) {
            $column = $dataTable->getColumnByName($inheritedColumnName);
            $columnPropertyPath = $column->getPropertyPath();
            if ($columnPropertyPath !== null) {
                $columnPropertyPath = $propertyPathPrefix.'.'.$columnPropertyPath;
            } else {
                $columnPropertyPath = $propertyPathPrefix;
            }
            $column->setOption('propertyPath', $columnPropertyPath);
        }
    }

    /**
     * @param DataTable $dataTable
     *
     * @return string[]
     */
    protected function getColumnNames(DataTable $dataTable): array
    {
        return array_map([$this, 'columnName'], $dataTable->getColumns());
    }

    /**
     * Callback used to filter columns.
     *
     * @param AbstractColumn $column
     *
     * @return string
     */
    private function columnName(AbstractColumn $column): string
    {
        return $column->getName();
    }
}