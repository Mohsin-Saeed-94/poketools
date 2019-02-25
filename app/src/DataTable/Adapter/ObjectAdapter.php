<?php
/**
 * @file ObjectAdapter.php
 */

namespace App\DataTable\Adapter;


use Omines\DataTablesBundle\Adapter\AdapterInterface;
use Omines\DataTablesBundle\Adapter\ArrayResultSet;
use Omines\DataTablesBundle\Adapter\ResultSetInterface;
use Omines\DataTablesBundle\Column\AbstractColumn;
use Omines\DataTablesBundle\DataTableState;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Use an array of objects (e.g. retrieved from a repository) as a data source.
 *
 * The main option is "data" which is either the data array itself or a callable
 * to retrieve the array.  Using a callable will defer data fetching until the
 * data is required.  The callable should have this signature:
 * ```
 * function (int $start, int $limit)
 * ```
 * When $limit is 0, all results should be returned.
 *
 * The other (optional) option is "count", an int or callable creating an int
 * containing the total number of rows.  This option is required if "data" is a
 * callable.
 */
class ObjectAdapter implements AdapterInterface
{
    /**
     * @var PropertyAccessorInterface
     */
    protected $accessor;

    /**
     * @var array|callable
     */
    protected $data;

    /**
     * @var int|callable|null
     */
    protected $count;

    /**
     * @var array
     */
    protected $options;

    /**
     * ObjectAdapter constructor.
     *
     * @param PropertyAccessorInterface $propertyAccessor
     */
    public function __construct(PropertyAccessorInterface $propertyAccessor)
    {
        $this->accessor = $propertyAccessor;
    }

    /**
     * {@inheritdoc}
     */
    public function configure(array $options)
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        $options = $resolver->resolve($options);
        $this->data = $options['data'];
        $this->count = $options['count'];
        unset($options['data'], $options['count']);
        $this->options = $options;
    }

    /**
     * @param OptionsResolver $resolver
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data', [])
            ->setAllowedTypes('data', ['callable', 'array'])
            ->setDefault('count', null)
            ->setAllowedTypes('count', ['callable', 'int', 'null']);
    }

    /**
     * Processes a datatable's state into a result set fit for further processing.
     *
     * @param DataTableState $state
     *
     * @return ResultSetInterface
     */
    public function getData(DataTableState $state): ResultSetInterface
    {
        // Fetch data
        $page = $this->pageData($state->getStart(), $state->getLength());
        $map = [];
        foreach ($state->getDataTable()->getColumns() as $column) {
            unset($propertyPath);
            $propertyPath = $column->getPropertyPath();
            $field = $column->getField() ?? $column->getName();
            $useDefaultPropertyPath = empty($propertyPath);
            if ($useDefaultPropertyPath && !empty($field)) {
                $propertyPath = $field;
            }
            if ($propertyPath !== null) {
                $map[$column->getName()] = $propertyPath;
            }
        }

        // Count total data available
        if ($this->count === null) {
            $totalCount = count($this->data);
        } elseif (is_callable($this->count)) {
            $totalCount = call_user_func($this->count);
        } else {
            $totalCount = $this->count;
        }

        $data = iterator_to_array($this->processData($state, $page, $map));
        $data = $this->orderData($state, $data);

        return new ArrayResultSet($data, $totalCount, count($data));
    }

    /**
     * Get the data to use.
     *
     * @param int $start
     * @param int $length
     *
     * @return array
     */
    protected function pageData(int $start, int $length)
    {
        if (is_callable($this->data)) {
            // Rely upon the data function to page the data if requested.
            return call_user_func($this->data, $start, $length);
        }

        if ($length > 0) {
            // Only get a portion of the data.
            return array_slice($this->data, $start, $length);
        }

        // Get all of the data.
        return $this->data;
    }

    /**
     * @param DataTableState $state
     * @param array $data
     * @param array $map
     *
     * @return \Generator
     */
    protected function processData(DataTableState $state, array $data, array $map)
    {
        $transformer = $state->getDataTable()->getTransformer();
        $search = $state->getGlobalSearch() ?: '';
        foreach ($data as $result) {
            $row = $this->processRow($state, $result, $map, $search);
            if (!empty($row)) {
                if (null !== $transformer) {
                    $row = $transformer($row, $result);
                }
                yield $row;
            }
        }
    }

    /**
     * @param DataTableState $state
     * @param object $result
     * @param array $map
     * @param string $search
     *
     * @return array|null
     */
    protected function processRow(DataTableState $state, object $result, array $map, string $search)
    {
        $row = [];

        // If there is no search query, don't bother with search logic to save time
        $searchMatch = empty($search);
        foreach ($state->getDataTable()->getColumns() as $column) {
            $propertyPath = $map[$column->getName()];
            $isAccessible = $this->accessor->isReadable($result, $propertyPath);
            if (!empty($propertyPath) && $isAccessible) {
                $value = $this->accessor->getValue($result, $propertyPath);
            } else {
                $value = null;
            }
            $value = $column->transform($value, $result);
            if (!$searchMatch) {
                $searchMatch = (false !== mb_stripos($value, $search));
            }
            $row[$column->getName()] = $value;
        }

        return $searchMatch ? $row : null;
    }

    /**
     * @param DataTableState $state
     * @param array $data
     *
     * @return array
     */
    protected function orderData(DataTableState $state, array $data): array
    {
        foreach ($state->getOrderBy() as [$column, $direction]) {
            /** @var AbstractColumn $column */
            if ($column->isOrderable()) {
                usort(
                    $data,
                    function (array $a, array $b) use ($column, $direction): int {
                        $aVal = $a[$column->getName()];
                        $bVal = $b[$column->getName()];
                        if ($direction === 'asc') {
                            // Sort ascending
                            $compareA = $aVal;
                            $compareB = $bVal;
                        } else {
                            // Sort descending
                            $compareA = $bVal;
                            $compareB = $aVal;
                        }

                        if (is_numeric($compareA) && is_numeric($compareB)) {
                            // Compare as ints if the data looks numeric.
                            return (int)$compareA - (int)$compareB;
                        }

                        // Otherwise compare naturally.
                        return strnatcmp($compareA, $compareB);
                    }
                );
            }
        }

        return $data;
    }
}
