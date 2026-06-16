<?php

declare(strict_types=1);

namespace App\Wallet\Infrastructure\Doctrine;

use App\Wallet\Domain\Wallet;
use App\Wallet\Domain\WalletRepositoryInterface;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;

class WallerRepository implements WalletRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ){}

    public function get(string $userId, string $currency, bool $lock = false): Wallet
    {
        $query = $this->entityManager->createQueryBuilder()
            ->select('w')
            ->from(Wallet::class, 'w')
            ->where('w.userId = :userId')
            ->andWhere('w.currency = :currency')
            ->setParameter('userId', $userId)
            ->setParameter('currency', $currency)
            ->getQuery();

        if ($lock) {
            $query->setLockMode(LockMode::PESSIMISTIC_WRITE);
        }

        $wallet = $query->getOneOrNullResult();

        if (!$wallet) {
            throw new \DomainException('Wallet not found.');
        }

        return $wallet;
    }

    public function save(Wallet $wallet): void
    {
        $this->entityManager->persist($wallet);
        $this->entityManager->flush();
    }
}
