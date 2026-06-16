<?php

declare(strict_types=1);

namespace App\Wallet\Domain\Event;

readonly class WalletCreated
{
    public function __construct(
        public string $walletId,
        public string $userId,
        public string $currency,
        public \DateTimeImmutable $createdAt
    ) {}
}
