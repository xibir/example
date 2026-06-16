<?php

declare(strict_types=1);

namespace App\Order\Domain;

use App\Order\Domain\ValueObject\OrderId;

interface OrderRepositoryInterface
{
    public function get(OrderId $orderId): Order;
}
