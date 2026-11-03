<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Repository\BookingRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class IntakeController
{
    public function __construct(
        private UserRepository $users,
        private BookingRepository $bookings,
    ) {
    }

    #[Route('/api/intake', methods: ['GET'])]
    public function show(Request $request): JsonResponse
    {
        $email = mb_strtolower(trim((string) $request->query->get('email')));
        if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            return new JsonResponse(['firstLesson' => true]);
        }

        $user = $this->users->findOneBy(['email' => $email]);
        if ($user === null) {
            return new JsonResponse(['firstLesson' => true]);
        }

        return new JsonResponse([
            'firstLesson' => !$this->bookings->hasPaidForUser($user),
        ]);
    }
}
