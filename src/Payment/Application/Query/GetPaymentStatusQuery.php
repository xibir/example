<?php

declare(strict_types=1);

namespace App\Payment\Application\Query;

use App\User\Domain\ValueObject\UserId;

final readonly class GetPaymentStatusQuery
{
    public function __construct(
        public string $paymentId,
        public UserId $userId,
    ) {}
}
