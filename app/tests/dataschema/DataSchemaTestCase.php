<?php
/**
 * @file DataSchemaTestCase.php
 */

namespace App\Tests\dataschema;

use Opis\JsonSchema\Schema;
use Opis\JsonSchema\ValidationError;
use Opis\JsonSchema\Validator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Parser;

/**
 * Base Test Case for schema validation
 */
abstract class DataSchemaTestCase extends TestCase
{
    protected const BASE_DIR_SCHEMA = __DIR__.'/../../resources/schema';
    protected const BASE_DIR_DATA = self::BASE_DIR_SCHEMA.'/../data';

    /**
     * Assert the data follows the schema.
     *
     * @param string $name
     * @param array|object $data
     * @param string|null $context
     */
    protected static function assertDataSchema(string $name, $data, ?string $context = null): void
    {
        static $schemas = [];
        if (!isset($schemas[$name])) {
            $schemaPath = realpath(sprintf('%s/%s.json', self::BASE_DIR_SCHEMA, $name));
            $schemas[$name] = Schema::fromJsonString(file_get_contents($schemaPath));
        }
        $validator = new Validator();
        $result = $validator->dataValidation($data, $schemas[$name]);
        self::assertTrue($result->isValid(), self::buildSchemaErrorMessage($result->getErrors(), $context));
    }

    /**
     * @param ValidationError[] $errors
     *
     * @param string|null $context
     *
     * @return string
     */
    private static function buildSchemaErrorMessage(array $errors, ?string $context = null): string
    {
        $header = 'Data does not follow schema:';
        if ($context === null) {
            $header = sprintf('[%s] %s', $context, $header);
        }
        $messages = [$header];
        foreach ($errors as $error) {
            $messages[] = sprintf("[%s]\n%s", $error->keyword(), json_encode($error->keywordArgs(), JSON_PRETTY_PRINT));
        }

        return implode("\n", $messages);
    }

    /**
     * Read a YAML file from the given path.
     *
     * @param string $filePath
     *
     * @return array
     */
    protected function getDataFromYaml(string $filePath): array
    {
        static $cache = [];
        if (!isset($cache[$filePath])) {
            $cache[$filePath] = $this->parseYaml(file_get_contents($filePath));
        }

        return $cache[$filePath];
    }

    /**
     * @param string $yaml
     *
     * @return array
     */
    protected function parseYaml(string $yaml): array
    {
        $data = $this->getYamlParser()->parse($yaml);
        self::assertNotEmpty($data, 'Data is empty');

        return $data;
    }

    /**
     * @return Parser
     */
    protected function getYamlParser(): Parser
    {
        static $parser = null;

        if (!isset($parser)) {
            $parser = new Parser();
        }

        return $parser;
    }
}
