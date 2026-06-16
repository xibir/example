<?php

declare(strict_types=1);

namespace App\Payment\UI\Http;

use App\Payment\Application\Command\PayOrderCommand;
use App\Shared\Application\CommandBus;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final readonly class PayOrderController
{
    public function __construct(
        private CommandBus $commandBus,
    ) {}

    #[Route('/api/orders/{orderId}/pay', methods: ['POST'])]
    public function __invoke(string $orderId, Request $request): JsonResponse
    {
        $payload = json_decode($request->getContent(), true, flags: JSON_THROW_ON_ERROR);

        $result = $this->commandBus->dispatch(new PayOrderCommand(
            orderId: $orderId,
            userId: $payload['userId'],
            amountMinor: $payload['amountMinor'],
            currency: $payload['currency'],
            transactionId: $payload['transactionId'],
        ));

        return new JsonResponse($result->toArray());
    }
}
