<?php

namespace App\User\Domain;

use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\UserId;

interface UserRepositoryInterface
{
    public function get(UserId $id, bool $lock = false): ?User;
    public function findByEmail(Email $email): ?User;

    public function existsByEmail(Email $email): bool;

    public function save(User $user): void;
}
