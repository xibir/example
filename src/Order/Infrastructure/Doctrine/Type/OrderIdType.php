<?php

declare(strict_types=1);

namespace App\Order\Infrastructure\Doctrine\Type;

use App\Order\Domain\ValueObject\OrderId;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Symfony\Component\Uid\Uuid;

final class OrderIdType extends Type
{
    public const NAME = 'order_id';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getGuidTypeDeclarationSQL($column);
    }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?OrderId
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof OrderId) {
            return $value;
        }

        if ($value instanceof Uuid) {
            return OrderId::fromString($value->toRfc4122());
        }

        if (is_string($value)) {
            return OrderId::fromString($value);
        }

        throw new \InvalidArgumentException(sprintf(
            'Could not convert value "%s" to OrderId.',
            $value,
        ));
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof OrderId) {
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
