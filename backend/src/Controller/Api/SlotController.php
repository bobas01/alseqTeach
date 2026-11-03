<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Http\ApiPresenter;
use App\Http\DateRangeParser;
use App\Repository\SlotRepository;
use App\Service\BookingService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/slots')]
final class SlotController
{
    public function __construct(
        private BookingService $bookings,
        private SlotRepository $slots,
        private DateRangeParser $dates,
        private ApiPresenter $presenter,
        private string $appTimezone,
    ) {
    }

    #[Route('', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $this->bookings->releaseExpiredHolds();
        [$from, $to] = $this->dates->parse($request);
        $slots = $this->slots->findAvailableBetween($from, $to, new \DateTimeImmutable());

        return new JsonResponse([
            'timezone' => $this->appTimezone,
            'slots' => array_map($this->presenter->slot(...), $slots),
        ]);
    }
}
