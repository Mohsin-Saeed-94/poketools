<?php
/**
 * @file Render.php
 */

namespace DragoonBoots\JsonSchemaBundle\Schema;


use DragoonBoots\JsonSchemaBundle\Exception\UnknownSchemaException;
use DragoonBoots\JsonSchemaBundle\Schema\Loader\PathLoader;
use DragoonBoots\JsonSchemaBundle\Schema\Type\AbstractSchemaType;
use DragoonBoots\JsonSchemaBundle\Schema\Type\SchemaObject;

/**
 * Inspect schemas for their info.
 */
class Inspector
{
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

        return new SchemaObject($schema->resolve(), $uriPrefix);
    }
}
