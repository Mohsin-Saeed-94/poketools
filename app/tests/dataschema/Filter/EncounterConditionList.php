<?php


namespace App\Tests\dataschema\Filter;


use App\Tests\Traits\YamlParserTrait;
use Ds\Map;
use Ds\Set;
use Opis\JsonSchema\IFilter;

/**
 * Ensure a list of encounter conditions/states exist.
 */
class EncounterConditionList implements IFilter
{

    use YamlParserTrait;

    private YamlIdentifierExists $conditionExists;

    /**
     * Map conditions to a set of states
     *
     * @var \Ds\Map
     */
    private Map $conditionStates;

    public function __construct()
    {
        $this->conditionExists = new YamlIdentifierExists('encounter_condition');
        $this->conditionStates = new Map();
    }

    /**
     * @inheritDoc
     */
    public function validate($data, array $args): bool
    {
        if (empty($data)) {
            return true;
        }
        $conditionSets = array_map('trim', explode(',', $data));
        foreach ($conditionSets as $conditionSet) {
            $parts = explode('/', $conditionSet);
            if (count($parts) != 2) {
                return false;
            }
            [$condition, $state] = $parts;
            if (!$this->conditionExists->validate($condition, [])) {
                return false;
            }
            if (!$this->conditionStates->hasKey($condition)) {
                // Lookup data
                $entity = $this->loadEntityYaml(sprintf('encounter_condition/%s', $condition));
                $states = new Set(array_map(fn(string $state) => $condition.'-'.$state, array_keys($entity['states'])));
                $this->conditionStates->put($condition, $states);
            }
            if (!$this->conditionStates->get($condition)->contains($state)) {
                return false;
            }
        }

        return true;
    }

}
