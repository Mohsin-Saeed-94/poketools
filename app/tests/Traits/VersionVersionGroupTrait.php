<?php


namespace App\Tests\Traits;


use Ds\Map;

/**
 * Lookup the given version's version group
 */
trait VersionVersionGroupTrait
{

    use CsvParserTrait;

    /**
     * Lookup the given version's version group
     *
     * @param string $version
     *
     * @return string
     */
    protected function getVersionVersionGroup(string $version): string
    {
        return $this->getVersions()->get($version);
    }

    /**
     * Get a map of versions to version groups
     */
    private function getVersions(): Map
    {
        static $versions = null;
        if (!isset($versions)) {
            $versions = new Map();
            $versionData = $this->getCsvReader('version');
            foreach ($versionData as $row) {
                $versions->put($row['identifier'], $row['version_group']);
            }
        }

        return $versions;
    }

}
