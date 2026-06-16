<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Outbox\Doctrine;
use App\Shared\Domain\ValueObject\Uuid;
use Doctrine\ORM\Mapping as ORM;

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
        #[ORM\Column(type: 'string', length: 64)]
        public readonly string $createdAt,
        #[ORM\Column(type: 'string', length: 64)]
        public readonly string $updatedAt,
    ) {}

    public static function fromEvent(object $event): self
    {
        $now = (new \DateTimeImmutable())->format('Y-m-d H:i:s');

        return new self(
            id: Uuid::newUuid(),
            type: $event::class,
            payload: json_encode($event, JSON_THROW_ON_ERROR),
            status: OutboxMessageStatus::NEW,
            createdAt: $now,
            updatedAt: $now,
        );
    }
}
