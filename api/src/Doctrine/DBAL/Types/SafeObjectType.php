<?php


namespace App\Doctrine\DBAL\Types;


use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ObjectType;

/**
 * Doctrine Object type that base64 encodes objects.
 */
class SafeObjectType extends ObjectType
{

    const SAFE_OBJECT = 'safe_object';

    /**
     * @inheritDoc
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        return base64_encode(parent::convertToDatabaseValue($value, $platform));
    }

    /**
     * @inheritDoc
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return parent::convertToPHPValue(base64_decode($value, true), $platform);
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return self::SAFE_OBJECT;
    }

}
