<?php

declare(strict_types=1);

namespace App\Payment\Domain;

enum PaymentStatus: string
{
    case STARTED = 'started';
    case SUCCEEDED = 'succeeded';
    case FAILED = 'failed';
}
