<?php

namespace App\Wallet\Domain;

interface WalletRepositoryInterface
{
    public function get(string $userId, string $currency, bool $lock = false): Wallet;
    public function save(Wallet $wallet): void;
}
