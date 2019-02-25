<?php
/**
 * @file PagingTrait.php
 */

namespace App\Repository;


use Doctrine\ORM\QueryBuilder;

/**
 * Helpers for paging queries.
 *
 * Trait PagingTrait
 */
trait PagingTrait
{
    /**
     * Set limits on the query
     *
     * @param QueryBuilder $qb
     * @param int $start
     *   If this is greater than 0, the results will start from this row (0-indexed)
     * @param int $limit
     *  If this is greater than 0, the results will be limited to this many rows.
     *
     * @return QueryBuilder
     */
    protected function pageQuery(QueryBuilder $qb, int $start = 0, int $limit = 0)
    {
        if ($start > 0) {
            $qb->setFirstResult($start);
        }

        if ($limit > 0) {
            $qb->setMaxResults($limit);
        }

        return $qb;
    }
}
