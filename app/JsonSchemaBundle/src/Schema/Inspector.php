<?php
/**
 * @file Render.php
 */

namespace DragoonBoots\JsonSchemaBundle\Schema;


use DragoonBoots\JsonSchemaBundle\Exception\InvalidSchemaException;
use DragoonBoots\JsonSchemaBundle\Exception\UnknownSchemaException;
use DragoonBoots\JsonSchemaBundle\Schema\Loader\PathLoader;
use DragoonBoots\JsonSchemaBundle\Schema\Type\AbstractSchemaType;
use Opis\JsonSchema\ISchema;

/**
 * Inspect schemas for their info.
 */
class Inspector
{
    private const ANNOTATIONS = [
        'title',
        'description',
        'default',
        'readOnly',
        'writeOnly',
        'examples',
    ];

    /**
     * @var PathLoader
     */
    private $schemaLoader;

    /**
     * Render constructor.
     *
     * @param PathLoader $schemaLoader
     */
    public function __construct(PathLoader $schemaLoader)
    {
        $this->schemaLoader = $schemaLoader;
    }

    /**
     * Get a list of all Schemas available, mapped by schema set name.
     *
     * @param string $uriPrefix
     *
     * @return AbstractSchemaType[]
     *   A map of URIs to SchemaType objects.
     */
    public function schemaList(string $uriPrefix): array
    {
        $list = [];

        foreach ($this->schemaLoader->getAvailable($uriPrefix) as $schemaUri) {
            $list[$schemaUri] = $this->getSchemaInfo($schemaUri, $uriPrefix);
        }

        return $list;
    }

    /**
     * Get the info for the available schemas.
     *
     * @param string $uri
     * @param string $uriPrefix
     *
     * @return AbstractSchemaType
     *
     * @throws UnknownSchemaException
     *   Thrown when the URI could not be found.
     */
    public function getSchemaInfo(string $uri, string $uriPrefix): AbstractSchemaType
    {
        $schema = $this->schemaLoader->loadSchema($uri);
        if ($schema === null) {
            throw new UnknownSchemaException($uri);
        }

        $resolved = $this->deepResolve($schema, $uriPrefix);

        return SchemaType::create($resolved, $uriPrefix);
    }

    /**
     * Resolve all nested references
     *
     * @param ISchema|object $schema
     * @param string $uriPrefix
     *
     * @return object
     */
    private function deepResolve($schema, string $uriPrefix = '')
    {
        if ($schema instanceof ISchema) {
            $schema = $schema->resolve();
        }

        foreach ($schema as $k => &$v) {
            if ($k === '$ref') {
                // Load the reference
                $ref = $this->schemaLoader->loadSchema($v);
                if ($ref === null) {
                    // Try a relative path
                    $uri = rtrim($uriPrefix, '/').'/'.ltrim($v, '/');
                    $ref = $this->schemaLoader->loadSchema($uri);
                }
                if ($ref !== null) {
                    $ref = $ref->resolve();

                    // Copy annotations (e.g. title, description) to the
                    // referenced schema
                    foreach (self::ANNOTATIONS as $annotationKey) {
                        if (isset($schema->{$annotationKey})) {
                            $ref->{$annotationKey} = $schema->{$annotationKey};
                        }
                    }
                    $schema = $this->deepResolve($ref);
                } else {
                    throw new InvalidSchemaException(
                        sprintf('Invalid reference from "%s" to "%s".', implode('.', $schema->path), $v)
                    );
                }
                break;
            }
            if (is_object($v) || isset(SchemaType::COMBINATION_TYPES[$k])) {
                $v = $this->deepResolve($v, $uriPrefix);
            }
        }

        return $schema;
    }

    /**
     * @param $value
     *
     * @return bool
     */
    private function isCombination($value): bool
    {
        if (!is_array($value) || empty($value)) {
            return false;
        }
        foreach (array_keys(SchemaType::COMBINATION_TYPES) as $combinationType) {
            if (isset($value[$combinationType])) {
                return true;
            }
        }

        return false;
    }
}
