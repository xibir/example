<?php

declare(strict_types=1);

namespace App\Wallet\Domain;

enum WalletStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case PENDING_VERIFICATION = 'pending_verification';
}
