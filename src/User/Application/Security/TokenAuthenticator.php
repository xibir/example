<?php

declare(strict_types=1);

namespace App\User\Application\Security;

use App\User\Application\Security\JWT\PasswordHashHelper;
use App\User\Application\Security\JWT\TokenManager;
use App\User\Domain\UserRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

final class TokenAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly TokenManager $tokenManager,
        private readonly PasswordHashHelper $passwordHashHelper,
    ) {}

    public function supports(Request $request): bool
    {
        return $request->headers->has('Auth-Token');
    }

    public function authenticate(Request $request): SelfValidatingPassport
    {
        $token = trim((string) $request->headers->get('Auth-Token'));

        if ($token === '') {
            throw new BadCredentialsException('Empty auth token.');
        }

        return new SelfValidatingPassport(
            new UserBadge($token, function (string $token) {
                $data = $this->tokenManager->getData($token);

                $user = $this->users->get($data->userId);

                if ($user === null || !$user->isActive()) {
                    throw new BadCredentialsException('Invalid auth token.');
                }

                if ($data->hash !== $this->passwordHashHelper->generatePasswordHash($user->getPasswordHash(), $data->generatedTimestamp)) {
                    throw new BadCredentialsException('Invalid auth token.');
                }

                return $user;
            })
        );
    }

    public function onAuthenticationSuccess(Request $request, $token, string $firewallName): null
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        return new JsonResponse([
            'error' => 'Unauthorized',
        ], Response::HTTP_UNAUTHORIZED);
    }
}
