<?php

declare(strict_types=1);

namespace App\Payment\Application\Command;

final readonly class PayOrderCommand
{
    public function __construct(
        public string $orderId,
        public string $userId,
        public int    $amountMinor,
        public string $currency,
        public string $transactionId,
    ) {}

    public function requestHash(): string
    {
        return hash('sha256', json_encode([
            'orderId' => $this->orderId,
            'userId' => $this->userId,
            'amountMinor' => $this->amountMinor,
            'currency' => $this->currency,
        ], JSON_THROW_ON_ERROR));
    }
}
