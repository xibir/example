<?php

declare(strict_types=1);

namespace App\Payment\Domain;

use App\Order\Domain\ValueObject\OrderId;
use App\Payment\Domain\ValueObject\PaymentId;
use App\User\Domain\ValueObject\UserId;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'payments')]
final class Payment
{
    private ?\DateTimeImmutable $paidAt = null;

    private function __construct(
        #[ORM\Id]
        #[ORM\Column(type: 'payment_id')]
        private PaymentId $id,
        #[ORM\Column(type: 'order_id')]
        private OrderId $orderId,
        #[ORM\Column(type: 'user_id')]
        private UserId $userId,
        #[ORM\Column(type: 'float')]
        private float $amountMinor,
        #[ORM\Column(type: 'string', length: 255)]
        private string $currency,
        #[ORM\Column(type: 'enum')]
        private PaymentStatus $status,
        #[ORM\Column(type: 'string', length: 255)]
        private string $transactionId,
        #[ORM\Column(type: 'date_immutable')]
        private \DateTimeImmutable $createdAt,
    ) {}

    public static function start(
        OrderId $orderId,
        UserId  $userId,
        int     $amountMinor,
        string  $currency,
        string  $transactionId,
    ): self {
        if ($amountMinor <= 0) {
            throw new \DomainException('Payment amount must be positive.');
        }

        return new self(
            id: PaymentId::newUuid(),
            orderId: $orderId,
            userId: $userId,
            amountMinor: $amountMinor,
            currency: $currency,
            status: PaymentStatus::STARTED,
            transactionId: $transactionId,
            createdAt: new \DateTimeImmutable(),
        );
    }

    public function belongsTo(UserId $userId): bool
    {
        return $this->userId()->equals($userId);
    }

    public function succeed(): void
    {
        if ($this->status !== PaymentStatus::STARTED) {
            throw new \DomainException('Only started payment can be succeeded.');
        }

        $this->status = PaymentStatus::SUCCEEDED;
        $this->paidAt = new \DateTimeImmutable();
    }

    public function id(): PaymentId
    {
        return $this->id;
    }

    public function orderId(): OrderId
    {
        return $this->orderId;
    }

    public function userId(): UserId
    {
        return $this->userId;
    }

    public function status(): PaymentStatus
    {
        return $this->status;
    }

    public function paidAt(): \DateTimeImmutable
    {
        if (!$this->paidAt) {
            throw new \LogicException('Payment is not paid yet.');
        }

        return $this->paidAt;
    }
}
