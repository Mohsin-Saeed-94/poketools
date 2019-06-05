<?php


namespace App\Tests\dataschema\Validator\MediaType;


use Opis\JsonSchema\IMediaType;

/**
 * Validate SVG markup
 */
class SvgMediaType implements IMediaType
{
    /**
     * @inheritDoc
     */
    public function validate(string $data, string $type): bool
    {
        // Support SVG Fragments
        if (strpos($data, '<svg') !== 0) {
            $data = '<svg xmlns="http://www.w3.org/2000/svg">'.$data.'</svg>';
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        if (strpos($finfo->buffer($data), 'image/svg') !== 0) {
            return false;
        }

        $svg = new \DOMDocument();

        return $svg->loadXML($data) !== false;
    }
}
