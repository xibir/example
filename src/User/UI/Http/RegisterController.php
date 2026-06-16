<?php

declare(strict_types=1);

namespace App\User\UI\Http;

use App\Shared\Application\CommandBus;
use App\User\Application\Command\RegisterUserCommand;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RegisterController extends AbstractController
{
    public function __construct(
        private CommandBus $commandBus,
    ) {}

    #[Route('/api/users/register', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $payload = json_decode($request->getContent(), true, flags: JSON_THROW_ON_ERROR);

            $result = $this->commandBus->dispatch(new RegisterUserCommand(
                email: $payload['email'],
                plainPassword: $payload['plainPassword'],
            ));

            return new JsonResponse($result);
        } catch (\DomainException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], Response::HTTP_CONFLICT);
        } catch (\Exception) {
            return new JsonResponse(['error' => 'Internal error'], Response::HTTP_BAD_REQUEST);
        }
    }
}
