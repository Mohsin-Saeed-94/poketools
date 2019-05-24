<?php
/**
 * @file SchemaController.php
 */

namespace DragoonBoots\JsonSchemaBundle\Controller;


use DragoonBoots\JsonSchemaBundle\Schema\Inspector;
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
     * SchemaController constructor.
     *
     * @param Inspector $schemaInspector
     */
    public function __construct(Inspector $schemaInspector)
    {
        $this->schemaInspector = $schemaInspector;
    }

    /**
     * Schema index
     *
     * @param string $schemaPrefix
     *
     * @return Response
     */
    public function index(string $schemaPrefix): Response
    {
        $schemas = $this->schemaInspector->schemaList($schemaPrefix);

        return $this->render(
            '@JsonSchema/schema/index.html.twig',
            [
                'schemas' => $schemas,
            ]
        );
    }

    public function show(string $relPath): Response {

    }
}
