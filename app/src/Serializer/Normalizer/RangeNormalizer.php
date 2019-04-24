<?php

namespace App\Serializer\Normalizer;

use App\Entity\Embeddable\Range;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

/**
 * Normalizer for Ranges
 */
class RangeNormalizer implements NormalizerInterface, DenormalizerInterface
{
    /**
     * @var ObjectNormalizer
     */
    private $normalizer;

    /**
     * RangeNormalizer constructor.
     *
     * @param ObjectNormalizer $normalizer
     */
    public function __construct(ObjectNormalizer $normalizer)
    {
        $this->normalizer = $normalizer;
    }

    /**
     * @inheritDoc
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof Range;
    }

    /**
     * @inheritDoc
     *
     * @param Range $object
     */
    public function normalize($object, $format = null, array $context = []): string
    {
        return (string)$object;
    }

    /**
     * @inheritDoc
     *
     * @return Range
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        return Range::fromString($data);
    }

    /**
     * @inheritDoc
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        if ($type !== Range::class || !is_string($data) || empty($data)) {
            return false;
        }

        // A range can be a single number if min and max are the same.
        if (is_numeric($data)) {
            return true;
        }

        // A range can be written as "1-5".  This will eliminate nonsense such as
        // "-5" (technically numeric and caught above)
        // "1-"
        // "-1-"
        if (substr_count($data, '-', 1) !== 1) {
            return false;
        }

        return strpos($data, '-', 1) !== strlen($data) - 1;
    }
}
