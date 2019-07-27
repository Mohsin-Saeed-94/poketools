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
            $data = '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">'.$data.'</svg>';
        }
        $data = sprintf(
                '<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "%s/dtd/svg11-flat-20110816.dtd">',
                __DIR__
            ).$data;

        $svg = new \DOMDocument();

        return $svg->loadXML($data, LIBXML_DTDLOAD | LIBXML_DTDATTR | LIBXML_DTDVALID) !== false;
    }
}
