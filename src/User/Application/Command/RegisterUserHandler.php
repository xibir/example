<?php

declare(strict_types=1);

namespace App\User\Application\Command;

use App\Shared\Application\Transactional;
use App\Shared\Infrastructure\Outbox\Doctrine\OutboxRepository;
use App\User\Application\DTO\RegisterUserResult;
use App\User\Domain\User;
use App\User\Domain\UserRepositoryInterface;
use App\User\Domain\ValueObject\Email;
use App\Wallet\Domain\Wallet;
use App\Wallet\Domain\WalletRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsMessageHandler]
final readonly class RegisterUserHandler
{
    public function __construct(
        private Transactional $transactional,
        private UserRepositoryInterface $users,
        private WalletRepositoryInterface $wallets,
        private UserPasswordHasherInterface $passwordHasher,
        private OutboxRepository $outbox,
    ) {}

    public function __invoke(RegisterUserCommand $command): RegisterUserResult
    {
        return $this->transactional->run(function () use ($command) {
            $email = Email::fromString($command->email);

            if ($this->users->existsByEmail($email)) {
                throw new \DomainException('User already exists.');
            }

            $user = User::register($email);

            $passwordHash = $this->passwordHasher->hashPassword($user, $command->plainPassword);
            $user->setPasswordHash($passwordHash);

            $wallet = Wallet::create(
                userId: $user->id(),
                currency: $command->defaultCurrency,
            );

            $this->users->save($user);
            $this->wallets->save($wallet);

            foreach ($user->pullDomainEvents() as $event) {
                $this->outbox->add($event);
            }

            foreach ($wallet->pullDomainEvents() as $event) {
                $this->outbox->add($event);
            }

            return new RegisterUserResult($user->id());
        });
    }
}
