<?php


namespace App\Tests\dataschema\Filter;


use App\Tests\Traits\DataFinderTrait;
use Ds\Set;
use Opis\JsonSchema\IFilter;

/**
 * Checks that the given identifier exists in a directory of YAML files.
 */
class YamlIdentifierExists implements IFilter
{

    use DataFinderTrait;

    /**
     * List of identifiers for the entity type
     *
     * @var Set
     */
    private Set $identifiers;

    /**
     * YamlIdentifierExists constructor.
     *
     * @param string $entityType
     */
    public function __construct(string $entityType)
    {
        $this->identifiers = new Set();

        $finder = $this->getFinderForDirectory($entityType)
            ->files()->name(['*.yaml', '*.yml']);
        foreach ($finder as $fileInfo) {
            $this->identifiers->add($fileInfo->getFilenameWithoutExtension());
        }
    }

    /**
     * @param $data
     * @param array $args
     *
     * @return bool
     */
    public function validate($data, array $args): bool
    {
        return $this->identifiers->contains((string)$data);
    }

}
