<?php

declare(strict_types=1);

namespace App\Payment\Infrastructure\Doctrine\Type;

use App\Payment\Domain\ValueObject\PaymentId;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Symfony\Component\Uid\Uuid;

class PaymentIdType extends Type
{
    public const NAME = 'payment_id';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getGuidTypeDeclarationSQL($column);
    }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?PaymentId
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof PaymentId) {
            return $value;
        }

        if ($value instanceof Uuid) {
            return PaymentId::fromString($value->toRfc4122());
        }

        if (is_string($value)) {
            return PaymentId::fromString($value);
        }

        throw new \InvalidArgumentException(sprintf(
            'Could not convert value "%s" to PaymentId.',
            $value,
        ));
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof PaymentId) {
            return $value->value();
        }

        if ($value instanceof Uuid) {
            return $value->toRfc4122();
        }

        if (is_string($value)) {
            return $value;
        }

        throw new \InvalidArgumentException(sprintf(
            'Could not convert value "%s" to database uuid.',
            $value,
        ));
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
