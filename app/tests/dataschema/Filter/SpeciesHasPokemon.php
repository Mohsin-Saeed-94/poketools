<?php


namespace App\Tests\dataschema\Filter;


use App\Tests\Traits\VersionVersionGroupTrait;
use App\Tests\Traits\YamlParserTrait;
use Ds\Map;
use Ds\Set;
use Opis\JsonSchema\IFilter;

/**
 * Ensure that a given pokemon exists for a given species
 *
 * Pass version *OR* versionGroup.
 *
 * args:
 * - version
 * - versionGroup
 * - species
 * */
class SpeciesHasPokemon implements IFilter
{

    use YamlParserTrait;
    use VersionVersionGroupTrait;

    /**
     * Map Species => version group => set of pokemon
     *
     * @var \Ds\Map
     */
    private Map $speciesPokemon;


    /**
     * SpeciesPokemonCombination constructor.
     */
    public function __construct()
    {
        $this->speciesPokemon = new Map();
    }

    /**
     * @param $data
     * @param array $args
     *
     * @return bool
     */
    public function validate($data, array $args): bool
    {
        if (isset($args['version'])) {
            $versionGroup = $this->getVersionVersionGroup($args['version']);
        } else {
            $versionGroup = $args['versionGroup'];
        }
        $species = $args['species'];

        if (!$this->speciesPokemon->hasKey($species)) {
            // Lookup data
            $entity = $this->loadEntityYaml(sprintf('pokemon/%s', $species));
            $versionGroupPokemon = new Map();
            foreach ($entity as $versionGroupSlug => $versionGroupData) {
                $versionGroupPokemon->put($versionGroupSlug, new Set(array_keys($versionGroupData['pokemon'])));
            }
            $this->speciesPokemon->put($species, $versionGroupPokemon);
        }

        return $this->speciesPokemon->get($species)
            ->get($versionGroup)
            ->contains($data);
    }

}
