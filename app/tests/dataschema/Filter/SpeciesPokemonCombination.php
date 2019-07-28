<?php


namespace App\Tests\dataschema\Filter;


use App\Tests\data\YamlParserTrait;
use Opis\JsonSchema\IFilter;

/**
 * Ensure that a species/pokemon string contains valid references
 *
 * args:
 * - versionGroup
 */
class SpeciesPokemonCombination implements IFilter
{
    use YamlParserTrait;

    /**
     * @var YamlIdentifierExists
     */
    private $yamlIdentifierExists;

    private $species = [];

    /**
     * SpeciesPokemonCombination constructor.
     */
    public function __construct()
    {
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

        [$species, $pokemon] = explode('/', $data, 2);

        // Ensure the species exists
        if (!$this->yamlIdentifierExists->validate($species, [])) {
            return false;
        }

        if (!isset($this->species[$species])) {
            $entity = $this->loadEntityYaml(sprintf('pokemon/%s', $species));
            foreach ($entity as $versionGroupSlug => $versionGroupData) {
                $this->species[$species][$versionGroupSlug] = array_fill_keys(
                    array_keys($versionGroupData['pokemon']),
                    0
                );
            }
        }

        return isset($this->species[$species][$versionGroup][$pokemon]);
    }
}
