<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Exception\DomainException;
use App\Http\ApiPresenter;
use App\Http\DateRangeParser;
use App\Repository\SlotRepository;
use App\Service\AdminAuth;
use App\Service\BookingService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/admin')]
final class AdminController
{
    public function __construct(
        private AdminAuth $auth,
        private BookingService $bookings,
        private SlotRepository $slots,
        private DateRangeParser $dates,
        private ApiPresenter $presenter,
        private string $appTimezone,
    ) {
    }

    #[Route('/login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);
        $password = is_array($payload) ? trim((string) ($payload['password'] ?? '')) : '';
        if ($password === '') {
            throw new DomainException('Informe a senha.', 422, 'invalid_payload');
        }

        return new JsonResponse(['token' => $this->auth->login($password)]);
    }

    #[Route('/slots', methods: ['GET'])]
    public function slots(Request $request): JsonResponse
    {
        $this->bookings->releaseExpiredHolds();
        [$from, $to] = $this->dates->parse($request);
        $slots = $this->slots->findBetween($from, $to);
        $slotIds = array_values(array_filter(array_map(static fn ($slot) => $slot->getId(), $slots)));
        $bySlot = $this->bookings->activeBookingsBySlotIds($slotIds);

        return new JsonResponse([
            'timezone' => $this->appTimezone,
            'slots' => array_map(
                fn ($slot) => $this->presenter->adminSlot($slot, $bySlot[$slot->getId()] ?? null),
                $slots,
            ),
        ]);
    }

    #[Route('/slots/{id}/block', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function block(int $id): JsonResponse
    {
        return new JsonResponse($this->presenter->adminSlot($this->bookings->blockSlot($id)));
    }

    #[Route('/slots/{id}/unblock', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function unblock(int $id): JsonResponse
    {
        return new JsonResponse($this->presenter->adminSlot($this->bookings->unblockSlot($id)));
    }

    #[Route('/bookings', methods: ['GET'])]
    public function bookings(): JsonResponse
    {
        $simulated = $this->bookings->isSimulatedPix();
        $upcoming = $this->bookings->listUpcoming();
        $items = array_map(
            fn ($booking) => $this->presenter->adminBooking($booking, $simulated),
            $upcoming,
        );

        return new JsonResponse([
            'bookings' => $items,
            'awaiting' => array_values(array_filter(
                $items,
                static fn (array $booking): bool => $booking['status'] === 'awaiting_teacher',
            )),
        ]);
    }

    #[Route('/bookings/{id}/approve', methods: ['POST'])]
    public function approve(string $id): JsonResponse
    {
        $booking = $this->bookings->teacherApprove($id);

        return new JsonResponse($this->presenter->adminBooking($booking, $this->bookings->isSimulatedPix()));
    }

    #[Route('/bookings/{id}/reject', methods: ['POST'])]
    public function reject(string $id): JsonResponse
    {
        $booking = $this->bookings->teacherReject($id);

        return new JsonResponse($this->presenter->adminBooking($booking, $this->bookings->isSimulatedPix()));
    }
}
