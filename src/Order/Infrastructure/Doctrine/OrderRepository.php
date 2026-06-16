<?php

declare(strict_types=1);

namespace App\Order\Infrastructure\Doctrine;

use App\Order\Domain\Order;
use App\Order\Domain\OrderRepositoryInterface;
use App\Order\Domain\ValueObject\OrderId;
use Doctrine\ORM\EntityManagerInterface;

readonly class OrderRepository implements OrderRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function get(OrderId $orderId, bool $lock = false): Order
    {
        return $this->entityManager->find(Order::class, $orderId->value());
    }
}
