<?php

declare(strict_types=1);

namespace App\Shared\Application;

use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;

final class CommandBus
{
    use HandleTrait {
        handle as private messengerHandle;
    }

    public function __construct(MessageBusInterface $messageBus)
    {
        $this->messageBus = $messageBus;
    }

    public function dispatch(object $command): mixed
    {
        return $this->messengerHandle($command);
    }
}
