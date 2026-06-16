<?php

declare(strict_types=1);

namespace App\User\Application\Security\JWT;

use App\User\Domain\User;

readonly class AuthenticationTokenGenerator
{
    public function __construct(
        private TokenManager $tokenManager,
        private PasswordHashHelper $passwordHashHelper,
        private string $authDomain,
        private int $authTokenExpireSeconds
    ) {}

    public function generate(User $user): string
    {
        $timestamp = time();
        $payload = [
            'iss' => $this->authDomain,
            'iat' => $timestamp,
            'sub' => $user->id()->value(),
            'exp' => $timestamp + $this->authTokenExpireSeconds,
            'hash' => $this->passwordHashHelper->generatePasswordHash($user->getPasswordHash(), $timestamp),
        ];

        return $this->tokenManager->generateToken($payload);
    }
}
