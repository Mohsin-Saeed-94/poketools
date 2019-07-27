<?php
/**
 * @file DataSchemaTestCase.php
 */

namespace App\Tests\dataschema;

use App\Tests\dataschema\Validator\MediaType\CommonMarkMediaType;
use App\Tests\dataschema\Validator\MediaType\MathMlMediaType;
use App\Tests\dataschema\Validator\MediaType\SvgMediaType;
use Opis\JsonSchema\IValidator;
use Opis\JsonSchema\Loaders\File;
use Opis\JsonSchema\Schema;
use Opis\JsonSchema\ValidationError;
use Opis\JsonSchema\Validator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Yaml\Parser;

/**
 * Base Test Case for schema validation
 */
abstract class DataSchemaTestCase extends TestCase
{
    protected const SCHEMA_BASE_URI = 'https://poketools.gamestuff.info/data/schema/';
    protected const BASE_DIR_SCHEMA = __DIR__.'/../../resources/schema/';
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
            $schemaPath = realpath(self::BASE_DIR_SCHEMA.$name.'.json');
            $schemas[$name] = Schema::fromJsonString(file_get_contents($schemaPath));
        }
        $validator = self::getValidator();

        // Kludge to get data to be properly encapsulated (arrays vs objects)
        $data = json_decode(json_encode($data, JSON_NUMERIC_CHECK | JSON_PRESERVE_ZERO_FRACTION), false);

        $result = $validator->schemaValidation($data, $schemas[$name], 1, $validator->getLoader());
        self::assertTrue($result->isValid(), self::buildSchemaErrorMessage($result->getErrors(), $context));
    }

    /**
     * @return IValidator
     */
    private static function getValidator(): IValidator
    {
        static $validator = null;
        if ($validator === null) {
            $loader = new File(self::SCHEMA_BASE_URI, [self::BASE_DIR_SCHEMA]);
            $validator = new Validator(null, $loader);
            self::addMediaTypes($validator);
        }

        return $validator;
    }

    /**
     * @param Validator $validator
     */
    private static function addMediaTypes(Validator $validator): void
    {
        $mediaTypes = [
            'text/markdown; variant=CommonMark' => new CommonMarkMediaType(),
            'application/mathml+xml' => new MathMlMediaType(),
            'image/svg+xml' => new SvgMediaType(),
        ];

        $mediaTypeContainer = $validator->getMediaType();
        foreach ($mediaTypes as $mime => $mediaType) {
            $mediaTypeContainer->add($mime, $mediaType);
        }
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
        $messages = [];
        foreach ($errors as $error) {
            $errorContext = $error->dataPointer();
            if ($context !== null) {
                array_unshift($errorContext, $context);
            }
            $messages[] = sprintf(
                "[%s]:\nCaused by: %s\nData: %s\nError:%s\n",
                implode('.', $errorContext),
                $error->keyword(),
                json_encode($error->data(), JSON_PRETTY_PRINT),
                json_encode($error->keywordArgs(), JSON_PRETTY_PRINT)
            );
            if ($error->subErrorsCount() > 0) {
                $messages[] = self::buildSchemaErrorMessage($error->subErrors(), $context);
            }
        }

        return "Data does not follow schema:\n".implode(str_repeat('-', 10)."\n", $messages);
    }

    /**
     * @return JsonEncoder
     */
    private static function getJsonEncoder(): JsonEncoder
    {
        static $encoder = null;
        if ($encoder === null) {
            $encoder = new JsonEncoder();
        }

        return $encoder;
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
