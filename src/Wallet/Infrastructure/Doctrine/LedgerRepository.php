<?php

declare(strict_types=1);

namespace App\Wallet\Infrastructure\Doctrine;

use App\Wallet\Domain\LedgerEntry;
use Doctrine\ORM\EntityManagerInterface;

final readonly class LedgerRepository
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {}

    public function save(LedgerEntry $ledgerEntry): void
    {
        $this->entityManager->persist($ledgerEntry);
        $this->entityManager->flush();
    }
}
