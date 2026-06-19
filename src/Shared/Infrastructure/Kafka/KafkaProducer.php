<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Kafka;

use RdKafka\Conf;
use RdKafka\Producer;

final class KafkaProducer
{
    private Producer $producer;

    public function __construct(
        private readonly string $kafkaBrokers,
    ) {
        $conf = new Conf();

        $conf->set('metadata.broker.list', $this->kafkaBrokers);

        $conf->set('acks', 'all');
        $conf->set('enable.idempotence', 'true');

        $this->producer = new Producer($conf);
    }

    public function publish(
        string $topicName,
        string $key,
        array $payload,
    ): void {
        $topic = $this->producer->newTopic($topicName);

        $topic->produce(
            RD_KAFKA_PARTITION_UA,
            0,
            json_encode($payload, JSON_THROW_ON_ERROR),
            $key
        );

        $this->producer->poll(0);

        $result = $this->producer->flush(10_000);

        if ($result !== RD_KAFKA_RESP_ERR_NO_ERROR) {
            throw new \RuntimeException('Kafka message was not flushed.');
        }
    }
}
