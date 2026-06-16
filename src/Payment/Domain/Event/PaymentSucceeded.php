<?php

declare(strict_types=1);

namespace App\Payment\Domain\Event;

final readonly class PaymentSucceeded
{
    public function __construct(
        public string $paymentId,
        public string $orderId,
        public string $userId,
        public int $amountMinor,
        public string $currency,
        public \DateTimeImmutable $paidAt,
    ) {}
}
