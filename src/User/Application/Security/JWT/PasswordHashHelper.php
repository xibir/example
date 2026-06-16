<?php

declare(strict_types=1);

namespace App\User\Application\Security\JWT;

class PasswordHashHelper
{
    public function generatePasswordHash(string $password, int $timestamp): string
    {
        return md5($password.$timestamp);
    }
}
