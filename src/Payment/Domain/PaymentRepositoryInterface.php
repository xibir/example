<?php

namespace App\Payment\Domain;

use App\Order\Domain\ValueObject\OrderId;
use App\Payment\Domain\ValueObject\PaymentId;

interface PaymentRepositoryInterface
{
    public function find(PaymentId $id): ?Payment;
    public function findSuccessfulByOrderId(OrderId $orderId): Payment;

    public function save(Payment $payment): void;
}
