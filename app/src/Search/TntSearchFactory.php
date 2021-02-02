<?php


namespace App\Search;


use Doctrine\DBAL\Connection;
use TeamTNT\TNTSearch\Stemmer\PorterStemmer;
use TeamTNT\TNTSearch\TNTSearch;

/**
 * Construct and configure TNTSearch
 */
class TntSearchFactory
{
    public static function create(string $searchIndexPath, Connection $sourceDb): TNTSearch
    {
        $tnt = new TNTSearch();
        $tnt->loadConfig(
            [
                'storage' => $searchIndexPath,
                'stemmer' => PorterStemmer::class,
            ] + self::getTntDatabaseConfig($sourceDb)
        );

        return $tnt;
    }

    /**
     * Create the database configuration from the given connection
     *
     * @param Connection $connection
     *
     * @return array
     * @throws \Doctrine\DBAL\Exception
     */
    private static function getTntDatabaseConfig(Connection $connection): array
    {
        $config = [];
        $config['driver'] = match ($connection->getDatabasePlatform()->getName()) {
            'sqlite' => 'sqlite',
            default => throw new \LogicException('Unsupported platform for search source')
        };

        if ($config['driver'] === 'sqlite') {
            $config['database'] = $connection->getDatabase();
        }

        return $config;
    }
}
