<?php

declare(strict_types=1);

namespace App\User\Application\Security\JWT;

use App\User\Domain\ValueObject\UserId;

readonly class JWTData
{
    public function __construct(
        public UserId $userId,
        public string $hash,
        public int $generatedTimestamp
    ) {}
}
