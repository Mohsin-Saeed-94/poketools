<?php


namespace App\Doctrine\DBAL\Types;


use Cake\Chronos\Chronos;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\TimeImmutableType;

/**
 * Time type using Chronos
 */
class ChronosTimeType extends TimeImmutableType
{

    const CHRONOS_TIME_TYPE = 'chronos_time';

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::CHRONOS_TIME_TYPE;
    }

    /**
     * {@inheritdoc}
     * @param Chronos $value
     *
     * @throws ConversionException
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (is_null($value)) {
            return $value;
        }

        if ($value instanceof Chronos) {
            return $value->format($platform->getTimeFormatString());
        }

        throw ConversionException::conversionFailedInvalidType(
            $value,
            $this->getName(),
            ['null', Chronos::class]
        );
    }

    /**
     * {@inheritdoc}
     * @return Chronos
     *
     * @throws ConversionException
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if (is_null($value) || $value instanceof Chronos) {
            return $value;
        }

        $chronos = Chronos::createFromFormat('!'.$platform->getTimeFormatString(), $value);

        if (!$chronos) {
            throw ConversionException::conversionFailedFormat(
                $value,
                $this->getName(),
                $platform->getTimeFormatString()
            );
        }

        return $chronos;
    }
}
