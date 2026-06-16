<?php

declare(strict_types=1);

namespace App\User\Infrastructure\Doctrine\Type;

use App\User\Domain\ValueObject\Email;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class EmailType extends Type
{
    public const NAME = 'email';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getStringTypeDeclarationSQL($column);
    }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?Email
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof Email) {
            return $value;
        }

        if (is_string($value)) {
            return Email::fromString($value);
        }

        throw new \InvalidArgumentException(sprintf(
            'Could not convert value "%s" to Email.',
            $value,
        ));
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof Email) {
            return $value->value();
        }

        if (is_string($value)) {
            return $value;
        }

        throw new \InvalidArgumentException(sprintf(
            'Could not convert value "%s" to database string.',
            $value,
        ));
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
