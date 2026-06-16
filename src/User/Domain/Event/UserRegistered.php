<?php

declare(strict_types=1);

namespace App\User\Domain\Event;

final readonly class UserRegistered
{
    public function __construct(
        public string $userId,
        public string $email,
        public \DateTimeImmutable $registeredAt
    ) {}
}
