<?php

declare(strict_types=1);

namespace App\User\Application\Security\JWT;

use App\User\Domain\ValueObject\UserId;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

readonly class TokenManager
{
    private const HASH_ALG = 'HS256';

    public function __construct(
        private string $authSecret,
        private string $authDomain,
    ) {}

    public function getData(string $token): JWTData
    {
        try {
            $payload = JWT::decode($token, new Key($this->authSecret, self::HASH_ALG));
        } catch (\UnexpectedValueException|\LogicException) {
            throw new BadCredentialsException('Invalid auth token');
        }

        if (!isset($payload->hash, $payload->sub) || $payload->iss !== $this->authDomain) {
            throw new BadCredentialsException('Invalid auth token');
        }

        return new JWTData(
            userId: UserId::fromString($payload->sub),
            hash: $payload->hash,
            generatedTimestamp: $payload->iat,
        );
    }

    public function generateToken(array $payload): string
    {
        return JWT::encode($payload, $this->authSecret, self::HASH_ALG);
    }
}
