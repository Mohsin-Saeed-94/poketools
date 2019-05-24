<?php
/**
 * @file UnknownUriPrefixException.php
 */

namespace DragoonBoots\JsonSchemaBundle\Exception;

use Throwable;

/**
 * Thrown when a URI Prefix not configured is used.
 */
class UnknownUriPrefixException extends \UnexpectedValueException
{
    /**
     * UnknownUriPrefixException constructor.
     *
     * @param string $uriPrefix
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(string $uriPrefix, $code = 0, Throwable $previous = null)
    {
        $message = sprintf('The URI Prefix "%s" is not known.  Check the JsonSchemaBundle configuration.', $uriPrefix);
        parent::__construct($message, $code, $previous);
    }
}
