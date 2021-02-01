<?php


namespace App\Tests\dataschema\Validator\MediaType;


use DOMDocument;
use Opis\JsonSchema\IMediaType;

/**
 * Validate MathML markup
 */
class MathMlMediaType implements IMediaType {

  /**
   * @inheritDoc
   */
  public function validate(string $data, string $type): bool {
    $data = sprintf(
        '<!DOCTYPE math PUBLIC "-//W3C//DTD MathML 2.0//EN" "%s/dtd/mathml2/mathml2.dtd">',
        __DIR__
      ) . $data;

    $math = new DOMDocument();

    return $math->loadXML($data, LIBXML_DTDLOAD | LIBXML_DTDATTR | LIBXML_DTDVALID) !== FALSE;
  }

}
