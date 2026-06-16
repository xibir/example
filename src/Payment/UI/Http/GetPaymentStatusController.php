<?php

declare(strict_types=1);

namespace App\Payment\UI\Http;

use App\Payment\Application\Query\GetPaymentStatusQuery;
use App\Shared\Application\QueryBus;
use App\User\Domain\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class GetPaymentStatusController extends AbstractController
{
    public function __construct(
        private QueryBus $queryBus,
    ) {}

    #[Route('/api/payments/{paymentId}', methods: ['GET'])]
    public function __invoke(string $paymentId, Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        $result = $this->queryBus->ask(new GetPaymentStatusQuery(
            paymentId: $paymentId,
            userId: $user->id(),
        ));

        return new JsonResponse($result);
    }
}
