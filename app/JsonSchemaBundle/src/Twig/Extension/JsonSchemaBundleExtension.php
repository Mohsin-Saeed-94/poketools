<?php
/**
 * @file JsonSchemaBundleExtension.php
 */

namespace DragoonBoots\JsonSchemaBundle\Twig\Extension;


use DragoonBoots\JsonSchemaBundle\Schema\Type\AbstractSchemaType;
use Twig\Extension\AbstractExtension;
use Twig\TwigTest;

/**
 * Class JsonSchemaBundleExtension
 */
class JsonSchemaBundleExtension extends AbstractExtension
{
    public function getTests()
    {
        return [
            new TwigTest('schema_type', [$this, 'isSchemaType']),
        ];
    }

    /**
     * @param $value
     *
     * @return bool
     */
    public function isSchemaType($value): bool
    {
        return $value instanceof AbstractSchemaType;
    }

}
