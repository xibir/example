<?php

declare(strict_types=1);

namespace App\User\Application\Command;

use App\Shared\Application\Transactional;
use App\Shared\Infrastructure\Outbox\Doctrine\OutboxRepository;
use App\User\Application\DTO\LoginUserResult;
use App\User\Application\Security\JWT\AuthenticationTokenGenerator;
use App\User\Domain\UserRepositoryInterface;
use App\User\Domain\ValueObject\Email;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsMessageHandler]
readonly class LoginUserHandler
{
    public function __construct(
        private Transactional                $transactional,
        private UserRepositoryInterface      $users,
        private UserPasswordHasherInterface  $passwordHasher,
        private OutboxRepository             $outbox,
        private AuthenticationTokenGenerator $tokenGenerator,
    ) {}

    public function __invoke(LoginUserCommand $command): LoginUserResult
    {
        return $this->transactional->run(function () use ($command) {
            $email = Email::fromString($command->email);

            $user = $this->users->findByEmail($email);

            if (is_null($user)) {
                throw new \DomainException('Invalid credentials.');
            }

            if (!$this->passwordHasher->isPasswordValid($user, $command->plainPassword)) {
                throw new \DomainException('Invalid credentials.');
            }

            $user->login($command->ip, $command->userAgent);

            foreach ($user->pullDomainEvents() as $event) {
                $this->outbox->add($event);
            }

            return new LoginUserResult(
                authToken: $this->tokenGenerator->generate($user)
            );
        });
    }
}
