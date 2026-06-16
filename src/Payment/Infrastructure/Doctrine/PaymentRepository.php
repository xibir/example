<?php

declare(strict_types=1);

namespace App\Payment\Infrastructure\Doctrine;

use App\Order\Domain\ValueObject\OrderId;
use App\Payment\Domain\Payment;
use App\Payment\Domain\PaymentRepositoryInterface;
use App\Payment\Domain\ValueObject\PaymentId;
use Doctrine\ORM\EntityManagerInterface;

final readonly class PaymentRepository implements PaymentRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {}

    public function find(PaymentId $id): ?Payment
    {
        return $this->entityManager->find(Payment::class, $id->value());
    }

    public function findSuccessfulByOrderId(OrderId $orderId): Payment
    {
        $payment = $this->entityManager->createQueryBuilder()
            ->select('p')
            ->from(Payment::class, 'p')
            ->where('p.orderId = :orderId')
            ->andWhere('p.status = :status')
            ->setParameter('orderId', $orderId->value())
            ->setParameter('status', 'succeeded')
            ->getQuery()
            ->getOneOrNullResult();

        if (!$payment) {
            throw new \DomainException('Successful payment was not found.');
        }

        return $payment;
    }

    public function save(Payment $payment): void
    {
        $this->entityManager->persist($payment);
        $this->entityManager->flush();
    }
}
