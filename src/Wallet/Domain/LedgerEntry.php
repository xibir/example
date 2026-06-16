<?php

declare(strict_types=1);

namespace App\Wallet\Domain;

use App\User\Domain\ValueObject\UserId;
use Symfony\Component\Uid\Uuid;

class LedgerEntry
{
    private function __construct(
        private string $id,
        private string $operationId,
        private UserId $userId,
        private string $direction,
        private float  $amount,
        private string $currency,
    ) {}

    public static function create(
        string $operationId,
        UserId $userId,
        string $direction,
        float  $amount,
        string $currency,
        string $createdAt,
    ): self {
      return new self(
          id: Uuid::v7()->toRfc4122(),
          operationId: $operationId,
          userId: $userId,
          direction: $direction,
          amount: $amount,
          currency: $currency,
      );
    }
}
