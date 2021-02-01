<?php


namespace App\Tests\dataschema\Validator\MediaType;


use Opis\JsonSchema\IMediaType;

/**
 * CommonMark media type for JsonSchema validator
 *
 * Does not perform any actual validation because all text is valid Markdown.
 *
 * @todo Search for markdown linters that can be helpful here
 */
class CommonMarkMediaType implements IMediaType {

  /**
   * @inheritDoc
   */
  public function validate(string $data, string $type): bool {
    return true;
  }

}
