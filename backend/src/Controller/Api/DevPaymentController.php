<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Http\ApiPresenter;
use App\Service\BookingService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class DevPaymentController
{
    public function __construct(
        private BookingService $bookings,
        private ApiPresenter $presenter,
    ) {
    }

    #[Route('/api/dev/bookings/{id}/simulate-payment', methods: ['POST'])]
    public function simulate(string $id): JsonResponse
    {
        $booking = $this->bookings->confirmStub($id);

        return new JsonResponse($this->presenter->booking($booking, true));
    }
}
