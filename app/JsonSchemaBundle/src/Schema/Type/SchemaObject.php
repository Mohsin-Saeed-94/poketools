<?php
/**
 * @file SchemaObject.php
 */

namespace DragoonBoots\JsonSchemaBundle\Schema\Type;

use DragoonBoots\JsonSchemaBundle\Schema\SchemaType;
use DragoonBoots\JsonSchemaBundle\Schema\Type\Combination\SchemaAnyOf;

/**
 * "object" type
 */
class SchemaObject extends AbstractSchemaCollectionType
{
    /**
     * @var int|null
     */
    protected $maxProperties;

    /**
     * @var int|null
     */
    protected $minProperties;

    /**
     * @var AbstractSchemaType[]|null
     */
    protected $required;

    /**
     * @var AbstractSchemaType[]|null
     */
    protected $properties;

    /**
     * @var AbstractSchemaType[]|null
     */
    protected $patternProperties;

    /**
     * @var AbstractSchemaType|bool
     */
    protected $additionalProperties = true;

    /**
     * @var array[]|AbstractSchemaType[]|null
     */
    protected $dependencies;

    /**
     * @var AbstractSchemaType|null
     */
    protected $propertyNames;

    /**
     * SchemaObject constructor.
     *
     * @param object $schema
     */
    public function __construct(object $schema)
    {
        parent::__construct($schema);

        $this->apply($schema);
    }

    /**
     * @param object $schema
     */
    public function apply(object $schema): void
    {
        parent::apply($schema);

        $this->maxProperties = $schema->maxProperties ?? null;
        $this->minProperties = $schema->minProperties ?? null;

        // Properties
        if (isset($schema->properties)) {
            if (!isset($this->properties)) {
                $this->properties = [];
            }
            foreach ($schema->properties as $propertyKey => $childSchema) {
                if (isset($this->properties[$propertyKey])) {
                    $this->properties[$propertyKey]->apply($childSchema);
                } else {
                    $child = SchemaType::create($childSchema);
                    $child->setParent($this);
                    $this->properties[$propertyKey] = $child;
                }
            }
        }

        // Pattern Properties
        if (isset($schema->patternProperties)) {
            if (!isset($this->patternProperties)) {
                $this->patternProperties = [];
            }
            foreach ($schema->patternProperties as $re => $childSchema) {
                if (isset($this->patternProperties[$re])) {
                    $this->patternProperties[$re]->apply($childSchema);
                } else {
                    $child = SchemaType::create($childSchema);
                    $child->setParent($this);
                    $this->patternProperties[$re] = $child;
                }
            }
        }

        // Required Properties
        if (isset($schema->required)) {
            if (!isset($this->required)) {
                $this->required = [];
            }
            foreach ($schema->required as $requiredKey) {
                $this->required[$requiredKey] = $this->findPropertySchema($requiredKey);
            }
        }

        // Additional properties
        if (isset($schema->additionalProperties)) {
            if (is_bool($schema->additionalProperties)) {
                $this->additionalProperties = $schema->additionalProperties;
            } elseif (is_object($schema->additionalProperties)) {
                if ($this->additionalProperties instanceof AbstractSchemaType) {
                    $this->additionalProperties->apply($schema->additionalProperties);
                } else {
                    $child = SchemaType::create($schema->additionalProperties);
                    $child->setParent($this);
                    $this->additionalProperties = $child;
                }
            }
        }
        // Dependencies
        if (isset($schema->dependencies)) {
            if (!isset($this->dependencies)) {
                $this->dependencies = [];
            }
            foreach ($schema->dependencies as $dependentKey => $dependency) {
                $this->dependencies[$dependentKey] = [];
                if (is_object($dependency)) {
                    // Specific schema dependencies
                    $this->dependencies[$dependentKey] = new \MergeSchema(
                        $dependency,
                        $this->findPropertySchema($dependentKey)
                    );
                } else {
                    // This is just a list of property names.
                    foreach ($dependency as $dependencyKey) {
                        $this->dependencies[$dependentKey][] = $this->findPropertySchema($dependencyKey);
                    }
                }
            }
        }

        // Property names
        if (isset($schema->propertyNames)) {
            // Property names must always be a string, so this is implied by the spec.
            $schema->propertyNames->type = 'string';
            $child = SchemaType::create($schema->propertyNames);
            $child->setParent($this);
            $this->propertyNames = $child;
        }
    }

    /**
     * Find the schema that matches the property name.
     *
     * @param string $propertyName
     *
     * @return AbstractSchemaType
     */
    private function findPropertySchema(string $propertyName): AbstractSchemaType
    {
        if (isset($this->properties[$propertyName])) {
            // The property is explicitly defined.
            return $this->properties[$propertyName];
        }

        if (!empty($this->patternProperties)) {
            // Try matching on patternProperties
            $matchedProperty = $this->matchPatternProperties($propertyName);
            if ($matchedProperty !== null) {
                return $matchedProperty;
            }
        }

        // The default is a property that will match anything
        $matchedProperty = SchemaType::create();
        $matchedProperty->setParent($this);

        return $matchedProperty;
    }

    /**
     * @param $propertyKey
     *
     * @return AbstractSchemaType|null
     */
    private function matchPatternProperties($propertyKey): ?AbstractSchemaType
    {
        /** @var AbstractSchemaType[] $matchedSchemas */
        $matchedSchemas = [];
        foreach ($this->patternProperties as $re => $patternPropertySchema) {
            if (preg_match('/'.preg_quote($re, '/').'/', $propertyKey) === 1) {
                $matchedSchemas[] = $patternPropertySchema;
            }
        }

        // Create a combination if multiple patterns match
        if (count($matchedSchemas) > 1) {
            /** @var SchemaAnyOf $combination */
            $combination = SchemaType::create((object)['anyOf' => []]);
            foreach ($matchedSchemas as $matchedSchema) {
                $combination->addChild($matchedSchema);
            }

            return $combination;
        }

        if (count($matchedSchemas) === 1) {
            $matched = $matchedSchemas[array_key_first($matchedSchemas)];
            $matched->setParent($this);

            return $matched;
        }

        return null;
    }

    /**
     * @return int|null
     */
    public function getMaxProperties(): ?int
    {
        return $this->maxProperties;
    }

    /**
     * @return int|null
     */
    public function getMinProperties(): ?int
    {
        return $this->minProperties;
    }

    /**
     * @return AbstractSchemaType[]|null
     */
    public function getRequired(): ?array
    {
        return $this->required;
    }

    /**
     * @return AbstractSchemaType[]|null
     */
    public function getProperties(): ?array
    {
        return $this->properties;
    }

    /**
     * @return AbstractSchemaType[]|null
     */
    public function getPatternProperties(): ?array
    {
        return $this->patternProperties;
    }

    /**
     * @return bool|AbstractSchemaType
     */
    public function getAdditionalProperties()
    {
        return $this->additionalProperties;
    }

    /**
     * @return array[]|AbstractSchemaType[]|null
     */
    public function getDependencies()
    {
        return $this->dependencies;
    }

    /**
     * @return AbstractSchemaType|null
     */
    public function getPropertyNames(): ?AbstractSchemaType
    {
        return $this->propertyNames;
    }
}
