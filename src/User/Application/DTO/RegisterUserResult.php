<?php

declare(strict_types=1);

namespace App\User\Application\DTO;

use App\User\Domain\ValueObject\UserId;

readonly class RegisterUserResult
{
    public function __construct(public UserId $userId) {}
}
