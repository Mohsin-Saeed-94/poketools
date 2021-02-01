<?php


namespace App\Tests\dataschema\Filter;

use App\Tests\Traits\YamlParserTrait;
use Ds\Map;
use Ds\Set;
use Opis\JsonSchema\IFilter;

/**
 * Verify Version Group uses the given Pokedex
 *
 * Args:
 * - versionGroup: Version group identifier
 */
class VersionGroupHasPokedex implements IFilter
{

    use YamlParserTrait;

    private Map $versionGroupPokedexes;

    public function __construct()
    {
        $this->versionGroupPokedexes = new Map();

        // Build map
        foreach ($this->buildYamlDataProvider('pokedex') as $identifier => $data) {
            $data = $data[0];
            $versionGroups = $data['version_groups'];
            foreach ($versionGroups as $versionGroup) {
                if (!$this->versionGroupPokedexes->hasKey($versionGroup)) {
                    $this->versionGroupPokedexes->put($versionGroup, new Set());
                }
                $this->versionGroupPokedexes[$versionGroup]->add($identifier);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function validate($data, array $args): bool
    {
        $versionGroup = $args['versionGroup'];

        return $this->versionGroupPokedexes->get($versionGroup)
            ->contains($data);
    }

}
