<?php

declare(strict_types=1);

namespace App\User\Application\Command;

class LoginUserCommand
{
    public function __construct(
        public string $email,
        public string $plainPassword,
        public string $ip,
        public string $userAgent,
    ) {}
}
