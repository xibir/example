<?php

declare(strict_types=1);

namespace App\User\Infrastructure\Doctrine;

use App\User\Domain\User;
use App\User\Domain\UserRepositoryInterface;
use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\UserId;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;

readonly class UserRepository implements UserRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function findByEmail(Email $email): ?User
    {
         return $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => $email->value()]);
    }

    public function existsByEmail(Email $email): bool
    {
        $user = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => $email->value()]);
        ;

        return !is_null($user);
    }

    public function save(User $user): void
    {
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    public function get(UserId $id, bool $lock = false): User
    {
        $lockMode = $lock ? LockMode::PESSIMISTIC_WRITE : null;

        return $this->entityManager
            ->getRepository(User::class)
            ->find($id, $lockMode);
    }
}
