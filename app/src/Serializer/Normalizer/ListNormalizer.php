<?php
/**
 * @file ListNormalizer.php
 */

namespace App\Serializer\Normalizer;


use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalizer for comma-separated lists of strings.
 */
class ListNormalizer implements NormalizerInterface, DenormalizerInterface
{

    /**
     * @inheritDoc
     */
    public function denormalize($data, $type, $format = null, array $context = [])
    {
        $array = explode(', ', $data);
        if ($type !== 'array') {
            $array = new $type($array);
        }

        return $array;
    }

    /**
     * @inheritDoc
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return is_string($data) && !empty($data) && $this->typeIsCollection($type);
    }

    /**
     * A type is a collection if it is an array or implements Doctrine\Common\Collections\Collection.
     *
     * @param string $type
     *
     * @return bool
     */
    private function typeIsCollection(string $type): bool
    {
        return $type === 'array'
            || is_a($type, Collection::class)
            || is_subclass_of($type, Collection::class);
    }

    /**
     * @inheritDoc
     *
     * @param array|Collection $object
     */
    public function normalize($object, $format = null, array $context = [])
    {
        if (!is_array($object)) {
            $object = $object->toArray();
        }

        return implode(', ', $object);
    }

    /**
     * @inheritDoc
     */
    public function supportsNormalization($data, $format = null)
    {
        if (!is_array($data) && !$this->typeIsCollection(get_class($data))) {
            return false;
        }

        foreach ($data as $datum) {
            if (!is_string($datum)) {
                return false;
            }
        }

        return true;
    }
}
