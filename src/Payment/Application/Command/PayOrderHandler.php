<?php

declare(strict_types=1);

namespace App\Payment\Application\Command;

use App\Order\Domain\OrderRepositoryInterface;
use App\Order\Domain\ValueObject\OrderId;
use App\Payment\Application\DTO\PayOrderResult;
use App\Payment\Domain\Event\PaymentSucceeded;
use App\Payment\Domain\Payment;
use App\Payment\Infrastructure\Doctrine\PaymentRepository;
use App\Shared\Application\Transactional;
use App\Shared\Infrastructure\Outbox\Doctrine\OutboxRepository;
use App\User\Domain\ValueObject\UserId;
use App\Wallet\Domain\LedgerEntry;
use App\Wallet\Domain\WalletRepositoryInterface;
use App\Wallet\Infrastructure\Doctrine\LedgerRepository;
use Symfony\Component\Lock\Key;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class PayOrderHandler
{
    public function __construct(
        private Transactional             $transactional,
        private LockFactory               $lockFactory,
        private OrderRepositoryInterface  $orders,
        private WalletRepositoryInterface $wallets,
        private PaymentRepository         $payments,
        private LedgerRepository          $ledger,
        private OutboxRepository          $outbox,
    ) {}

    public function __invoke(PayOrderCommand $command): PayOrderResult
    {
        return $this->transactional->run(function () use ($command): PayOrderResult {
            $requestHash = $command->requestHash();

            $key = new Key('pay_order' . $command->transactionId . $requestHash);
            $lock = $this->lockFactory->createLockFromKey($key);

            if (!$lock->acquire()) {
                throw new \DomainException('Order does not belong to user.');
            }

            $order = $this->orders->get(OrderId::fromString($command->orderId));

            if ($order->isPaid()) {
                $payment = $this->payments->findSuccessfulByOrderId($order->id());

                $result = PayOrderResult::alreadyPaid(
                    paymentId: $payment->id()->value(),
                    orderId: $order->id()->value(),
                );

                $lock->release();

                return $result;
            }

            if (!$order->belongsTo(UserId::fromString($command->userId))) {
                throw new \DomainException('Order does not belong to user.');
            }

            $order->assertPayableAmount(
                amountMinor: $command->amountMinor,
                currency: $command->currency,
            );

            $wallet = $this->wallets->get(
                userId: $command->userId,
                currency: $command->currency,
                lock: true
            );

            $payment = Payment::start(
                orderId: OrderId::fromString($command->orderId),
                userId: UserId::fromString($command->userId),
                amountMinor: $command->amountMinor,
                currency: $command->currency,
                transactionId: $command->transactionId,
            );

            $wallet->debit($command->amountMinor);
            $payment->succeed();
            $order->markPaid($payment->id());

            $this->payments->save($payment);

            $now = (new \DateTimeImmutable())->format('Y-m-d H:i:s');

            $ledgerEntryFrom = LedgerEntry::create(
                operationId: $payment->id()->value(),
                userId: UserId::fromString($command->userId),
                direction: 'credit',
                amount: $command->amountMinor,
                currency: $command->currency,
                createdAt: $now,
            );

            $ledgerEntryTo = LedgerEntry::create(
                operationId: $payment->id()->value(),
                userId: $order->merchantId(),
                direction: 'deposit',
                amount: $command->amountMinor,
                currency: $command->currency,
                createdAt: $now,
            );
            $this->ledger->save($ledgerEntryFrom);
            $this->ledger->save($ledgerEntryTo);

            $this->outbox->add(new PaymentSucceeded(
                paymentId: $payment->id()->value(),
                orderId: $order->id()->value(),
                userId: $command->userId,
                amountMinor: $command->amountMinor,
                currency: $command->currency,
                paidAt: $payment->paidAt(),
            ));

            $result = PayOrderResult::paid(
                paymentId: $payment->id()->value(),
                orderId: $order->id()->value(),
            );

            $lock->release();

            return $result;
        });
    }
}
