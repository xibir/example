<?php

namespace App\Shared\Infrastructure\Outbox\Doctrine;

enum OutboxMessageStatus: string
{
    case NEW = 'new';
    case FAILED = 'failed';
    case PROCESSING = 'processing';
    case PROCESSED = 'processed';
}
