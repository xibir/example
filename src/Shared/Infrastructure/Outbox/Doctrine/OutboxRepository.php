<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Outbox\Doctrine;

use Doctrine\ORM\EntityManagerInterface;

final readonly class OutboxRepository
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function add(object $event): void
    {
        $message = OutboxMessage::fromEvent($event);

        $this->entityManager->persist($message);
        $this->entityManager->flush();
    }
}
