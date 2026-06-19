<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Outbox\Doctrine;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

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

    public function releaseExpiredLocks(): void
    {
        $query = $this->entityManager->createQuery('
                UPDATE App\Shared\Infrastructure\Outbox\Doctrine\OutboxMessage m
                SET m.status = :newStatus, m.lockedUntil = NULL
                WHERE m.status = :statusProcessing
                AND m.lockedUntil < CURRENT_TIMESTAMP()
        ');
        $query->setParameters([
            'newStatus' => OutboxMessageStatus::NEW,
            'statusProcessing' => OutboxMessageStatus::PROCESSING,
        ]);
        $query->execute();
    }

    /**
     * @return OutboxMessage[]
     */
    public function claimBatch(int $limit): array
    {
        return $this->entityManager->getConnection()->transactional(function () use ($limit) {
            $messages = $this->entityManager
                ->createQueryBuilder()
                ->select('message')
                ->from(OutboxMessage::class, 'message')
                ->where('message.nextAttemptAt < CURRENT_TIMESTAMP() OR message.nextAttemptAt IS NULL')
                ->andWhere('message.status IN (:status)')
                ->setParameter('status', [OutboxMessageStatus::FAILED, OutboxMessageStatus::NEW])
                ->setMaxResults($limit)
                ->getQuery()
                ->setLockMode(LockMode::PESSIMISTIC_WRITE)
                ->getResult()
            ;

            if ($messages === []) {
                return [];
            }

            $ids = array_map(function (OutboxMessage $message) {
                return $message->id->toBinary();
            }, $messages);

            $this->entityManager->createQuery('
                    UPDATE App\Shared\Infrastructure\Outbox\Doctrine\OutboxMessage m
                    SET m.status = :newStatus, m.lockedUntil = :lockedUntil
                    WHERE m.id IN (:ids)
                ')
                ->setParameters([
                    'newStatus' => OutboxMessageStatus::PROCESSING,
                    'lockedUntil' => (new \DateTimeImmutable())->add(new \DateInterval('PT5S')),
                    'ids' => $ids,
                ])
                ->execute()
            ;

            return $messages;
        });
    }

    public function markPublished(Uuid $id): void
    {
        $message = $this->entityManager->getRepository(OutboxMessage::class)
            ->find($id->toBinary())
        ;
        $message->status = OutboxMessageStatus::PROCESSED;
        $message->lockedUntil = null;

        $this->entityManager->persist($message);
        $this->entityManager->flush();
    }

    public function markFailed(Uuid $id, \Throwable|\Exception $e): void
    {
        $message = $this->entityManager->getRepository(OutboxMessage::class)
            ->find($id->toBinary())
        ;
        $message->status = OutboxMessageStatus::FAILED;
        $message->lockedUntil = null;

        $this->entityManager->persist($message);
        $this->entityManager->flush();
    }
}
