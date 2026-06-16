<?php

declare(strict_types=1);

namespace App\Payment\Application\DTO;

class GetPaymentStatusResult
{
    public function __construct(
        public string $paymentId,
        public string $orderId,
        public string $status,
    ) {}
}
