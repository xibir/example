<?php

declare(strict_types=1);

namespace App\Shared\Application;

use Doctrine\ORM\EntityManagerInterface;

final readonly class Transactional
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {}

    public function run(callable $callback): mixed
    {
        return $this->entityManager->wrapInTransaction($callback);
    }
}
