<?php

declare(strict_types=1);

namespace App\Shared\Application;

use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;

final class QueryBus
{
    use HandleTrait {
        handle as private messengerHandle;
    }

    public function __construct(MessageBusInterface $messageBus)
    {
        $this->messageBus = $messageBus;
    }

    public function ask(object $query): mixed
    {
        return $this->messengerHandle($query);
    }
}
