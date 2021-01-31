<?php


namespace App\A2B\Drivers\Destination;


use Doctrine\Bundle\DoctrineBundle\ConnectionFactory;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use DragoonBoots\A2B\Annotations\DataMigration;
use DragoonBoots\A2B\Annotations\Driver;
use DragoonBoots\A2B\Drivers\AbstractDestinationDriver;
use DragoonBoots\A2B\Drivers\DestinationDriverInterface;
use DragoonBoots\A2B\Exception\BadUriException;
use DragoonBoots\A2B\Exception\MigrationException;

/**
 * Doctrine DBAL source driver.
 *
 * Only supports Postgres.
 *
 * Supports a few modes of operation:
 * - Single table/single row (standard data format): pass write() an array of data,
 *   keyed by column name.
 * - Single table/multiple row: pass write() a list of arrays; each array is the
 *   standard data format
 * - Multi table/single row: pass write() an array keyed by table name; each
 *   second-level array is the standard data format.
 * - Multi-table/multi row: pass write() an array keyed by table name; each
 *   second-level array is a list of arrays that match the standard data format.
 *
 * @Driver()
 */
class DbalDestinationDriver extends AbstractDestinationDriver implements DestinationDriverInterface
{

    /**
     * @var ConnectionFactory
     */
    protected $connectionFactory;

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * The base table for the current migration
     *
     * @var string|null
     */
    protected $baseTable;

    /**
     * The current list of tables.
     *
     * @var string[]
     */
    protected $tables = [];

    /**
     * A map of table names to their id fields.
     *
     * @var array
     */
    protected $tableIds = [];

    /**
     * A list of existing ids.  Used to determine UPDATE or INSERT.
     *
     * @var array
     */
    protected $existingIds;

    /**
     * DbalDestinationDriver constructor.
     *
     * @param ConnectionFactory $connectionFactory
     */
    public function __construct(ConnectionFactory $connectionFactory)
    {
        parent::__construct();

        $this->connectionFactory = $connectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function configure(DataMigration $definition)
    {
        parent::configure($definition);

        $this->baseTable = parse_url($this->migrationDefinition->getDestination(), PHP_URL_FRAGMENT);
        $this->tables = [$this->baseTable];
        $this->tableIds = [];
        foreach ($this->ids as $destId) {
            $this->tableIds[$this->baseTable][] = $destId->getName();
        }

        $destination = $definition->getDestination();
        $destination = str_replace('#'.$this->baseTable, '', $destination);

        try {
            $this->connection = $this->connectionFactory->createConnection(['url' => $destination]);
        } catch (DBALException $e) {
            // Convert the Doctrine exception into our own.
            throw new BadUriException($destination, $e->getCode(), $e);
        }

        $this->connection->beginTransaction();
    }

    /**
     * {@inheritdoc}
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Exception
     */
    public function getExistingIds(): array
    {
        try {
            $qb = $this->connection->createQueryBuilder();
            foreach ($this->ids as $destId) {
                $qb->addSelect($destId->getName());
            }
            $qb->from($this->connection->quoteIdentifier($this->baseTable));
            $results = $qb->execute();

            $this->existingIds = [];
            foreach ($results as $idRow) {
                $id = [];
                foreach ($this->ids as $destId) {
                    $id[$destId->getName()] = $this->resolveIdType($destId, $idRow[$destId->getName()]);
                }
                $this->existingIds[] = $id;
            }

            return $this->existingIds;
        } catch (\Exception $e) {
            $this->connection->rollBack();
            throw $e;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function read(array $destIds)
    {
        // With multitable support, trying to read existing data causes too many problems.
        // The written data will always overwrite existing data.
        return null;
    }

    /**
     * {@inheritdoc}
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Exception
     */
    public function write($data): ?array
    {
        try {
            $tableDataset = [];
            if ($this->isMultiTable()) {
                if (!isset($data[$this->baseTable])) {
                    throw new MigrationException(
                        "The data to be written does not include data for the base table.\n".var_export($data, true)
                    );
                }
                $tableDataset = $data;
            } else {
                $tableDataset[$this->baseTable] = $data;
            }

            $ids = $this->getIdsFromNewData($tableDataset[$this->baseTable]);

            foreach ($tableDataset as $table => $tableData) {
                $this->upsert($table, $this->tableIds[$table], $tableData);
            }

            return $ids;
        } catch (\Exception $e) {
            $this->connection->rollBack();
            throw $e;
        }
    }

    /**
     * Does the data to be written include data for multiple tables?
     *
     * @return bool
     */
    protected function isMultiTable(): bool
    {
        return (count($this->tables) > 1);
    }

    /**
     * Get id values from the data
     *
     * @param array $data
     *
     * @return array
     */
    protected function getIdsFromNewData(array $data)
    {
        $ids = [];
        foreach ($this->ids as $destId) {
            if (isset($data[$destId->getName()])) {
                $ids[$destId->getName()] = $data[$destId->getName()];
            } else {
                $ids[$destId->getName()] = null;
            }
        }

        return $ids;
    }

    /**
     * Perform a Postgres "Upsert" operation
     *
     * @see https://www.postgresql.org/docs/current/sql-insert.html
     *
     * @param string $table
     *   The table name to insert into
     * @param array $ids
     *   A list of ids for this table
     * @param array $data
     *   Key-value data array
     *
     * @return int
     *   The number of rows affected
     *
     * @throws DBALException
     */
    protected function upsert(string $table, array $ids, array $data): int
    {
        if (empty($data)) {
            // No data to change
            return 0;
        }
        if (!$this->isMultiRow($data)) {
            $data = [$data];
        }
        $fields = array_keys($data[0]);

        // Values, params, and update set
        $params = [];
        $paramKey = 0;
        $updateSet = [];
        $values = [];
        foreach ($data as $dataRow) {
            $valueRow = [];
            foreach ($dataRow as $key => $value) {
                $param = 'value_'.$paramKey;
                $paramKey++;
                $params[$param] = $value;
                $valueRow[] = ":${param}";
            }
            $values[] = $valueRow;
        }
        foreach ($data[0] as $key => $value) {
            $key = $this->connection->quoteIdentifier($key);
            $updateSet[] = "${key} = EXCLUDED.${key}";
        }

        $keysString = implode(', ', array_map([$this->connection, 'quoteIdentifier'], $ids));
        $fieldsString = implode(', ', array_map([$this->connection, 'quoteIdentifier'], $fields));
        $valuesStrings = [];
        foreach ($values as $valueRow) {
            $valuesStrings[] = '('.implode(', ', $valueRow).')';
        }
        $valuesString = implode(', ', $valuesStrings);
        $updateSetString = implode(', ', $updateSet);
        $sql = <<<SQL
INSERT INTO ${table} (${fieldsString})
VALUES ${valuesString}
ON CONFLICT (${keysString})
DO UPDATE 
SET
${updateSetString}
;
SQL;

        return $this->connection->executeUpdate($sql, $params);
    }

    /**
     * Is this a multi-row data set?
     *
     * @param array $data
     *
     * @return bool
     */
    protected function isMultiRow(array $data)
    {
        foreach ($data as $datum) {
            if (is_array($datum)) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function flush()
    {
        parent::flush();

        $this->connection->commit();
    }

    /**
     * Add additional tables to manage.
     *
     * @param string $table
     * @param array $ids
     *
     * @return $this
     */
    public function addTable(string $table, array $ids)
    {
        $this->tables[] = $table;
        $this->tableIds[$table] = $ids;

        return $this;
    }
}
