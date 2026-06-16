<?php

declare(strict_types=1);

namespace App\User\Domain\Event;

class UserLoggedIn
{
    public function __construct(
        public string $userId,
        public string $ip,
        public string $userAgent,
        public \DateTimeImmutable $loggedInAt
    ) {}
}
