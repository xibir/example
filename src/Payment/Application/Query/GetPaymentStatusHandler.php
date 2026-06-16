<?php

declare(strict_types=1);

namespace App\Payment\Application\Query;

use App\Payment\Application\DTO\GetPaymentStatusResult;
use App\Payment\Domain\ValueObject\PaymentId;
use App\Payment\Infrastructure\Doctrine\PaymentRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetPaymentStatusHandler
{
    public function __construct(
        private PaymentRepository $paymentRepository,
    ) {}

    public function __invoke(GetPaymentStatusQuery $query): GetPaymentStatusResult
    {
        $payment = $this->paymentRepository->find(PaymentId::fromString($query->paymentId));

        if (!$payment->belongsTo($query->userId)) {
            throw new \DomainException('Payment not found.');
        }

        return new GetPaymentStatusResult(
            paymentId: $payment->id()->value(),
            orderId: $payment->orderId()->value(),
            status: $payment->status()->value,
        );
    }
}
