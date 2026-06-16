<?php

namespace App\Shared\Infrastructure\Outbox\Doctrine;

enum OutboxMessageStatus: string
{
    case NEW = 'new';
}
