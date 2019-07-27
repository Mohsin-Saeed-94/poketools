<?php


namespace App\Tests\dataschema\Filter;


use App\Tests\data\DataFinderTrait;
use Opis\JsonSchema\IFilter;

/**
 * Checks that the given identifier exists in a directory of YAML files.
 */
class YamlIdentifierExists implements IFilter
{
    use DataFinderTrait;
    /**
     * @var string
     */
    private $entityType;

    /**
     * @var array
     */
    private $identifiers = [];

    /**
     * YamlIdentifierExists constructor.
     *
     * @param string $entityType
     */
    public function __construct(string $entityType)
    {
        $this->entityType = $entityType;
        $this->init();
    }

    /**
     * Get the list of identifiers
     */
    private function init()
    {
        $finder = $this->getFinderForDirectory($this->entityType);
        $finder->name('*.yaml');
        foreach ($finder as $fileInfo) {
            $this->identifiers[] = $fileInfo->getBasename('.'.$fileInfo->getExtension());
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
        return in_array($data, $this->identifiers, true);
    }
}
