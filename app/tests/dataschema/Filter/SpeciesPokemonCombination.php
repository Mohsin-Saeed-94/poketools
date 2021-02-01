<?php


namespace App\Tests\dataschema\Filter;


use App\Tests\Traits\YamlParserTrait;

/**
 * Ensure that a species/pokemon string contains valid references
 *
 * args:
 * - versionGroup
 */
class SpeciesPokemonCombination extends SpeciesHasPokemon
{

    use YamlParserTrait;

    /**
     * @var YamlIdentifierExists
     */
    private $yamlIdentifierExists;

    /**
     * SpeciesPokemonCombination constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->yamlIdentifierExists = new YamlIdentifierExists('pokemon');
    }

    /**
     * @param $data
     * @param array $args
     *
     * @return bool
     */
    public function validate($data, array $args): bool
    {
        $versionGroup = $args['versionGroup'];

        $parts = explode('/', $data);
        if (count($parts) != 2) {
            return false;
        }
        [$species, $pokemon] = $parts;

        // Ensure the species exists
        if (!$this->yamlIdentifierExists->validate($species, [])) {
            return false;
        }

        return parent::validate(
            $pokemon,
            [
                'species' => $species,
                'versionGroup' => $versionGroup,
            ]
        );
    }

}
