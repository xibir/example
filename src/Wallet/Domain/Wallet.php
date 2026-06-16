<?php

declare(strict_types=1);

namespace App\Wallet\Domain;

use App\User\Domain\ValueObject\UserId;
use App\Wallet\Domain\Event\WalletCreated;
use App\Wallet\Domain\ValueObject\WalletId;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'wallets')]
final class Wallet
{
    private array $events = [];

    private function __construct(
        #[ORM\Id]
        #[ORM\Column(type: 'uuid')]
        private WalletId $id,
        #[ORM\Column(type: 'uuid')]
        private UserId $userId,
        #[ORM\Column(type: 'string', nullable: false)]
        private string $currency,
        #[ORM\Column(type: 'integer')]
        private int $balanceMinor,
        #[ORM\Column(type: 'enum')]
        private WalletStatus $status,
        private \DateTimeImmutable $createdAt,
        private \DateTimeImmutable $updatedAt,
    ) {}

    public static function create(
        UserId $userId,
        string $currency,
    ): self {
        if ($currency === '') {
            throw new \DomainException('Currency is required.');
        }

        $now = new \DateTimeImmutable();

        $wallet = new self(
            id: WalletId::newUuid(),
            userId: $userId,
            currency: $currency,
            balanceMinor: 0,
            status: WalletStatus::PENDING_VERIFICATION,
            createdAt: $now,
            updatedAt: $now,
        );

        $wallet->record(new WalletCreated(
            walletId: $wallet->id()->value(),
            userId: $userId->value(),
            currency: $currency,
            createdAt: $now,
        ));

        return $wallet;
    }

    public function credit(int $amountMinor): void
    {
        if ($amountMinor <= 0) {
            throw new \DomainException('Amount must be positive.');
        }

        $this->balanceMinor += $amountMinor;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function debit(int $amountMinor): void
    {
        if ($amountMinor <= 0) {
            throw new \DomainException('Amount must be positive.');
        }

        if ($this->balanceMinor < $amountMinor) {
            throw new \DomainException('Insufficient funds.');
        }

        $this->balanceMinor -= $amountMinor;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function id(): WalletId
    {
        return $this->id;
    }

    public function userId(): UserId
    {
        return $this->userId;
    }

    public function currency(): string
    {
        return $this->currency;
    }

    public function balanceMinor(): int
    {
        return $this->balanceMinor;
    }

    private function record(object $event): void
    {
        $this->events[] = $event;
    }

    public function pullDomainEvents(): array
    {
        $events = $this->events;
        $this->events = [];

        return $events;
    }
}
