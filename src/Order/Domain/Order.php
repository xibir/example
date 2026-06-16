<?php

declare(strict_types=1);

namespace App\Order\Domain;

use App\Order\Domain\ValueObject\OrderId;
use App\Payment\Domain\ValueObject\PaymentId;
use App\User\Domain\ValueObject\UserId;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'orders')]
final class Order
{
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(type: 'order_id')]
        private OrderId $id,
        #[ORM\Column(type: 'user_id')]
        private UserId $userId,
        #[ORM\Column(type: 'user_id')]
        private UserId $merchantId,
        #[ORM\Column(type: 'float')]
        private float $amountMinor,
        #[ORM\Column(type: 'string')]
        private string $currency,
        #[ORM\Column(type: 'string')]
        private string $status = 'new',
        #[ORM\Column(type: 'payment_id')]
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
