<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Outbox\Command;

use App\Shared\Infrastructure\Kafka\KafkaProducer;
use App\Shared\Infrastructure\Outbox\Doctrine\OutboxRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:outbox:publish',
    description: 'Publishes outbox messages to Kafka.',
)]
final class PublishOutboxMessagesCommand extends Command
{
    public function __construct(
        private readonly OutboxRepository $outbox,
        private readonly KafkaProducer $producer,
    ) {
        parent::__construct();
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output,
    ): int {
        $this->outbox->releaseExpiredLocks();

        while (true) {
            $messages = $this->outbox->claimBatch(100);

            if ($messages === []) {
                usleep(500_000);
                continue;
            }

            foreach ($messages as $message) {
                try {
                    $topic = $this->topicFor($message->type);

                    $payload = [
                        'event_id' => $message->id,
                        'type' => $message->type,
                        'occurred_at' => $message->createdAt,
                        'payload' => json_decode($message->payload, true, flags: JSON_THROW_ON_ERROR),
                    ];

                    $this->producer->publish(
                        topicName: $topic,
                        key: $message->id->toRfc4122(),
                        payload: $payload,
                    );

                    $this->outbox->markPublished($message->id);
                } catch (\Throwable $e) {
                    $this->outbox->markFailed($message->id, $e);
                }
            }
        }

        return Command::SUCCESS;
    }

    private function topicFor(string $type): string
    {
        return 'app.events';
    }
}
