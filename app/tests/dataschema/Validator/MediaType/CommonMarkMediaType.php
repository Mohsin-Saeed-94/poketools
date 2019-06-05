<?php


namespace App\Tests\dataschema\Validator\MediaType;


use Opis\JsonSchema\IMediaType;

/**
 * CommonMark media type for JsonSchema validator
 *
 * Does not perform any actual validation because all text is valid Markdown.
 */
class CommonMarkMediaType implements IMediaType
{

    /**
     * @inheritDoc
     */
    public function validate(string $data, string $type): bool
    {
        $finfo = new \finfo(FILEINFO_MIME_TYPE);

        return $finfo->buffer($data) === 'text/plain';
    }
}
