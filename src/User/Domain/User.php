<?php

declare(strict_types=1);

namespace App\User\Domain;

use App\User\Domain\Event\UserLoggedIn;
use App\User\Domain\Event\UserRegistered;
use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\UserId;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity]
#[ORM\Table(name: 'users')]
final class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    private ?string $passwordHash = null;
    private array $events = [];

    private function __construct(
        #[ORM\Id]
        #[ORM\Column(type: 'user_id')]
        private UserId $id,
        #[ORM\Column(type: 'email', length: 320, unique: true)]
        public Email $email,
        #[ORM\Column(type: 'enum', enumType: UserStatus::class)]
        private UserStatus $status,
        public \DateTimeImmutable $createdAt,
        public \DateTimeImmutable $updatedAt,
    ) {}

    public static function register(Email $email): self
    {
        $now = new \DateTimeImmutable();

        $user = new self(
            id: UserId::newUuid(),
            email: $email,
            status: UserStatus::ACTIVE,
            createdAt: $now,
            updatedAt: $now,
        );

        $user->record(new UserRegistered(
            userId: $user->id()->value(),
            email: $email->value(),
            registeredAt: $now,
        ));

        return $user;
    }

    public function login(string $ip, string $userAgent): void
    {
        $now = new \DateTimeImmutable();

        $this->record(new UserLoggedIn(
            userId: $this->id()->value(),
            ip: $ip,
            userAgent: $userAgent,
            loggedInAt: $now
        ));

        $this->updatedAt = $now;
    }

    public function id(): UserId
    {
        return $this->id;
    }

    public function setPasswordHash(string $passwordHash): void
    {
        if ($passwordHash === '') {
            throw new \DomainException('Password hash cannot be empty.');
        }

        $this->passwordHash = $passwordHash;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }

    public function isActive(): bool
    {
        return $this->status === UserStatus::ACTIVE;
    }

    private function record(object $event): void
    {
        $this->events[] = $event;
    }

    public function pullDomainEvents(): array
    {
        $events = $this->events;
        $this->events = [];

        return $events;
    }

    public function getPassword(): ?string
    {
        return $this->passwordHash;
    }

    public function getRoles(): array
    {
        return ['ROLE_USER'];
    }

    public function eraseCredentials(): void
    {
    }

    public function getUserIdentifier(): string
    {
        return $this->id->value();
    }
}
