<?php
/**
 * @file UnknownSchemaException.php
 */

namespace DragoonBoots\JsonSchemaBundle\Exception;

use Throwable;

/**
 * Thrown when a schema is not known to the system.
 */
class UnknownSchemaException extends \UnexpectedValueException
{
    /**
     * UnknownSchemaException constructor.
     *
     * @param string $schemaUri
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(string $schemaUri, $code = 0, Throwable $previous = null)
    {
        $message = sprintf('The schema "%s" is not in any of the configured directories.', $schemaUri);
        parent::__construct($message, $code, $previous);
    }
}
