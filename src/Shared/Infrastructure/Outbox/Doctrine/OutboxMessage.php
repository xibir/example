<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Outbox\Doctrine;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'outbox_messages')]
final class OutboxMessage
{
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(type: 'uuid', unique: true)]
        public readonly Uuid $id,
        #[ORM\Column(type: 'string', length: 255)]
        public readonly string $type,
        #[ORM\Column(type: 'jsonb')]
        public readonly string $payload,
        #[ORM\Column(type: 'enum')]
        public OutboxMessageStatus $status,
        #[ORM\Column(type: 'date_immutable')]
        public readonly \DateTimeImmutable $updatedAt,
        #[ORM\Column(type: 'date_immutable')]
        public readonly \DateTimeImmutable $createdAt,
        #[ORM\Column(type: 'date_immutable')]
        public ?\DateTimeImmutable $lockedUntil = null,
        #[ORM\Column(type: 'date_immutable')]
        public ?\DateTimeImmutable $nextAttemptAt = null,
    ) {}

    public static function fromEvent(object $event): self
    {
        $now = new \DateTimeImmutable();

        return new self(
            id: Uuid::v7(),
            type: $event::class,
            payload: json_encode($event, JSON_THROW_ON_ERROR),
            status: OutboxMessageStatus::NEW,
            updatedAt: $now,
            createdAt: $now,
        );
    }
}
