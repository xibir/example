<?php

declare(strict_types=1);

namespace App\Order\Domain;

use App\Order\Domain\ValueObject\OrderId;
use App\Payment\Domain\ValueObject\PaymentId;
use App\User\Domain\ValueObject\UserId;

final class Order
{
    public function __construct(
        private OrderId $id,
        private UserId $userId,
        private UserId $merchantId,
        private int $amountMinor,
        private string $currency,
        private string $status = 'new',
        private ?PaymentId $paymentId = null,
    ) {}

    public function id(): OrderId
    {
        return $this->id;
    }

    public function merchantId(): UserId
    {
        return $this->merchantId;
    }

    public function belongsTo(UserId $userId): bool
    {
        return $this->userId === $userId;
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function assertPayableAmount(int $amountMinor, string $currency): void
    {
        if ($this->amountMinor !== $amountMinor || $this->currency !== $currency) {
            throw new \DomainException('Payment amount does not match order amount.');
        }
    }

    public function markPaid(PaymentId $paymentId): void
    {
        if ($this->isPaid()) {
            throw new \DomainException('Order is already paid.');
        }

        $this->status = 'paid';
        $this->paymentId = $paymentId;
    }
}
