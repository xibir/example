<?php

declare(strict_types=1);

namespace App\User\Infrastructure\Doctrine\Type;

use App\User\Domain\ValueObject\UserId;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Symfony\Component\Uid\Uuid;

final class UserIdType extends Type
{
    public const NAME = 'user_id';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getGuidTypeDeclarationSQL($column);
    }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?UserId
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof UserId) {
            return $value;
        }

        if ($value instanceof Uuid) {
            return UserId::fromString($value->toRfc4122());
        }

        if (is_string($value)) {
            return UserId::fromString($value);
        }

        throw new \InvalidArgumentException(sprintf(
            'Could not convert value "%s" to UserId.',
            $value,
        ));
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof UserId) {
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
