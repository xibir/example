<?php

declare(strict_types=1);

namespace App\Payment\Application\DTO;

final readonly class PayOrderResult
{
    private function __construct(
        public string $paymentId,
        public string $orderId,
        public string $status,
    ) {}

    public static function paid(string $paymentId, string $orderId): self
    {
        return new self($paymentId, $orderId, 'paid');
    }

    public static function alreadyPaid(string $paymentId, string $orderId): self
    {
        return new self($paymentId, $orderId, 'already_paid');
    }

    public static function fromArray(array $data): self
    {
        return new self(
            paymentId: $data['paymentId'],
            orderId: $data['orderId'],
            status: $data['status'],
        );
    }

    public function toArray(): array
    {
        return [
            'paymentId' => $this->paymentId,
            'orderId' => $this->orderId,
            'status' => $this->status,
        ];
    }
}
