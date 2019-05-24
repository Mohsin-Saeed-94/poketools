<?php
/**
 * @file Instantiator.php
 */

namespace DragoonBoots\JsonSchemaBundle\Schema;


use DragoonBoots\JsonSchemaBundle\Exception\InvalidSchemaException;
use DragoonBoots\JsonSchemaBundle\Schema\Type\AbstractSchemaType;
use DragoonBoots\JsonSchemaBundle\Schema\Type\Combination\SchemaAllOf;
use DragoonBoots\JsonSchemaBundle\Schema\Type\Combination\SchemaAnyOf;
use DragoonBoots\JsonSchemaBundle\Schema\Type\Combination\SchemaNot;
use DragoonBoots\JsonSchemaBundle\Schema\Type\Combination\SchemaOneOf;
use DragoonBoots\JsonSchemaBundle\Schema\Type\SchemaArray;
use DragoonBoots\JsonSchemaBundle\Schema\Type\SchemaBoolean;
use DragoonBoots\JsonSchemaBundle\Schema\Type\SchemaInteger;
use DragoonBoots\JsonSchemaBundle\Schema\Type\SchemaNumber;
use DragoonBoots\JsonSchemaBundle\Schema\Type\SchemaObject;
use DragoonBoots\JsonSchemaBundle\Schema\Type\SchemaString;

/**
 * Utility class to create SchemaType objects.
 */
class SchemaType
{
    public const TYPES = [
        'string' => SchemaString::class,
        'integer' => SchemaInteger::class,
        'number' => SchemaNumber::class,
        'object' => SchemaObject::class,
        'array' => SchemaArray::class,
        'boolean' => SchemaBoolean::class,
        'null' => '',
    ];

    public const COMBINATION_TYPES = [
        'allOf' => SchemaAllOf::class,
        'anyOf' => SchemaAnyOf::class,
        'oneOf' => SchemaOneOf::class,
        'not' => SchemaNot::class,
    ];

    /**
     * @param object|null $schema
     * @param string|null $prefix
     *
     * @return AbstractSchemaType
     */
    public static function create(?object $schema = null, ?string $prefix = null): AbstractSchemaType
    {
        // Default to something matching literally anything.
        if (!isset($schema)) {
            $schema = (object)[];
        }

        // Create the relative id if possible
        if (isset($schema->{'$id'}, $prefix)) {
            $schema->{'$relativeId'} = ltrim(str_replace($prefix, '', $schema->{'$id'}), '/');
        }

        // Create a combination
        foreach (self::COMBINATION_TYPES as $combinationType => $class) {
            if (isset($schema->{$combinationType})) {
                return new $class($schema, $prefix);
            }
        }

        // The default when no type is set is all types.
        if (!isset($schema->type)) {
            $schema->type = array_keys(self::TYPES);
        }

        // If multiple types are set, construct a oneOf combination per the spec
        // http://json-schema.org/latest/json-schema-validation.html#rfc.section.6.1.1
        if (is_array($schema->type)) {
            $templateSchema = clone $schema;
            // Remove identifying properties from the template used for the combination.
            foreach (['$id', '$schema'] as $removeProperty) {
                $templateSchema->{$removeProperty} = null;
            }
            $children = [];
            foreach ($schema->type as $typeName) {
                $childSchema = clone $templateSchema;
                $childSchema->type = $typeName;
                $children[] = $childSchema;
            }
            $schema->anyOf = $children;

            return new SchemaAnyOf($schema);
        }

        // By this point any case involving no type or multiple types is handled.
        if (!isset(self::TYPES[$schema->type])) {
            throw new InvalidSchemaException(
                sprintf(
                    'The child schema type "%s" is not valid.',
                    $schema->type
                )
            );
        }

        $type = self::TYPES[$schema->type];
        /** @var AbstractSchemaType $child */
        $child = new $type($schema, $prefix);

        return $child;
    }
}
