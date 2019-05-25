<?php
/**
 * @file SchemaController.php
 */

namespace DragoonBoots\JsonSchemaBundle\Controller;


use DragoonBoots\JsonSchemaBundle\Schema\Inspector;
use DragoonBoots\JsonSchemaBundle\Schema\Type\AbstractSchemaType;
use DragoonBoots\JsonSchemaBundle\Schema\Type\Combination\AbstractSchemaCombination;
use DragoonBoots\JsonSchemaBundle\Schema\Type\SchemaArray;
use DragoonBoots\JsonSchemaBundle\Schema\Type\SchemaObject;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

/**
 * Control data schema presentation
 */
class SchemaController extends AbstractController
{
    /**
     * @var Inspector
     */
    private $schemaInspector;

    /**
     * @var string
     */
    private $schemaPath;

    /**
     * @var string
     */
    private $schemaPrefix;

    /**
     * SchemaController constructor.
     *
     * @param Inspector $schemaInspector
     * @param string $schemaPath
     * @param string $schemaPrefix
     */
    public function __construct(Inspector $schemaInspector, string $schemaPath, string $schemaPrefix)
    {
        $this->schemaInspector = $schemaInspector;
        $this->schemaPath = $schemaPath;
        $this->schemaPrefix = $schemaPrefix;
    }

    /**
     * Schema index
     *
     * @return Response
     */
    public function index(): Response
    {
        $schemas = $this->schemaInspector->schemaList($this->schemaPrefix);

        return $this->render(
            '@JsonSchema/schema/index.html.twig',
            [
                'schemas' => $schemas,
            ]
        );
    }

    /**
     * Schema info page
     *
     * @param string $relPath
     *
     * @return Response
     */
    public function show(string $relPath): Response
    {
        $schema = $this->schemaInspector->getSchemaInfo($this->schemaPrefix.'/'.$relPath, $this->schemaPrefix);
        $json = file_get_contents($this->schemaPath.'/'.$relPath);

        $requirementsTree = $this->buildRequirementsTree($schema);

        return $this->render(
            '@JsonSchema/schema/show.html.twig',
            [
                'schemas' => $this->schemaInspector->schemaList($this->schemaPrefix),
                'schema' => $schema,
                'requirements_tree' => $requirementsTree,
                'json' => $json,
            ]
        );
    }

    /**
     * @param AbstractSchemaType $schema
     * @param bool $root
     *
     * @return array
     */
    private function buildRequirementsTree(AbstractSchemaType $schema, $root = true): array
    {
        $tree = [];

        if ($schema instanceof SchemaArray) {
            if (is_array($schema->getItems())) {
                foreach ($schema->getItems() as $item) {
                    $tree['items'][] = $this->buildRequirementsTree($item, false);
                }
            } elseif ($schema->getItems() instanceof AbstractSchemaType) {
                $tree['items'] = $this->buildRequirementsTree($schema->getItems(), false);
            }
            if ($schema->getAdditionalItems() instanceof AbstractSchemaType) {
                $tree['additionalItems'] = $this->buildRequirementsTree($schema->getAdditionalItems(), false);
            }
            if ($schema->getContains() instanceof AbstractSchemaType) {
                $tree['contains'] = $this->buildRequirementsTree($schema->getContains(), false);
            }
        } elseif ($schema instanceof SchemaObject) {
            if ($schema->getProperties() !== null) {
                foreach ($schema->getProperties() as $property => $propertySchema) {
                    $tree['properties'][$property] = $this->buildRequirementsTree($propertySchema, false);
                }
            }
            if ($schema->getPatternProperties() !== null) {
                foreach ($schema->getPatternProperties() as $pattern => $propertySchema) {
                    $tree['patternProperties'][$pattern] = $this->buildRequirementsTree($propertySchema, false);
                }
            }
            if ($schema->getAdditionalProperties() instanceof AbstractSchemaType) {
                $tree['additionalProperties'] = $this->buildRequirementsTree($schema->getAdditionalProperties(), false);
            }
            if ($schema->getPropertyNames() instanceof AbstractSchemaType) {
                $tree['propertyNames'] = $this->buildRequirementsTree($schema->getPropertyNames(), false);
            }
            if ($schema->getDependencies() !== null) {
                foreach ($schema->getDependencies() as $dependent => $dependency) {
                    if ($dependency instanceof AbstractSchemaType) {
                        $tree['dependencies'][$dependent] = $this->buildRequirementsTree($dependency, false);
                    }
                }
            }
        } elseif ($schema instanceof AbstractSchemaCombination && $root === true) {
            foreach ($schema->getChildren() as $child) {
                $tree[] = $this->buildRequirementsTree($child, false);
            }
        }

        return $tree;
    }
}
