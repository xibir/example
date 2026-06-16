<?php

declare(strict_types=1);

namespace App\Shared\Domain\ValueObject;

use Symfony\Component\Uid\UuidV7;

class Uuid extends UuidV7
{
    final private function __construct(?string $uuid = null) {
        parent::__construct($uuid);
    }

    public static function newUuid(): static
    {
        return new static();
    }

    public function value(): string
    {
        return $this->toRfc4122();
    }
}
