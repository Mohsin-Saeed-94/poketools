<?php
/**
 * @file ProcessedOrmAdapter.php
 */

namespace App\DataTable\Adapter;


use Doctrine\ORM\QueryBuilder;
use Omines\DataTablesBundle\Adapter\AdapterQuery;
use Omines\DataTablesBundle\Adapter\Doctrine\ORMAdapter;
use Omines\DataTablesBundle\DataTableState;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProcessedOrmAdapter extends ORMAdapter
{
    /**
     * @var callable
     */
    protected $rowProcessor;

    /**
     * @var array
     */
    protected $results;

    /**
     * @var array
     */
    protected $options;

    /**
     * {@inheritdoc}
     */
    public function configure(array $options)
    {
        parent::configure($options);

        $this->rowProcessor = $options['processor'];
        unset($options['processor']);

        $this->options = $options;
    }

    /**
     * {@inheritdoc}}
     */
    protected function prepareQuery(AdapterQuery $query)
    {
        $this->results = [];

        parent::prepareQuery($query);
    }

    /**
     * {@inheritdoc}}
     */
    protected function buildCriteria(QueryBuilder $queryBuilder, DataTableState $state)
    {
        $this->results = [];

        parent::buildCriteria($queryBuilder, $state);
    }

    /**
     * {@inheritdoc}
     *
     * Dummy method.  This value is overridden at the end of the process with actual
     * numbers generated
     */
    protected function getCount(QueryBuilder $queryBuilder, $identifier)
    {
        return 0;
    }

    /**
     * {@inheritdoc}
     */
    protected function getResults(AdapterQuery $query): \Traversable
    {
        $state = $query->getState();
        if (empty($this->results)) {
            // Force the OrmAdapter to get all results.  Not the most efficient, but
            // there is no way to know what or how many rows might be filtered out
            // in the row processor.
            /** @var QueryBuilder $qb */
            $qb = $query->get('qb');
            $start = $state->getStart();
            $state->setStart(0);
            $qb->setFirstResult(0);
            $length = $state->getLength();
            $state->setLength(0);
            $qb->setMaxResults(null);

            $results = parent::getResults($query);
            foreach ($results as $row) {
                $processed = call_user_func($this->rowProcessor, $row, $state);
                if ($processed !== null) {
                    $this->results[] = $processed;
                }
            }

            // Restore state
            $state->setStart($start);
            $state->setLength($length);

            // Set the count
            if (!$query->getTotalRows()) {
                $query->setTotalRows(count($this->results));
                $query->setFilteredRows($query->getTotalRows());
            } else {
                $query->setFilteredRows(count($this->results));
            }
        }

        return new \ArrayIterator(array_slice($this->results, $state->getStart(), $state->getLength()));
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefault(
            'processor',
            function ($row) {
                return $row;
            }
        )
            ->setAllowedTypes('processor', ['callable']);
    }


}