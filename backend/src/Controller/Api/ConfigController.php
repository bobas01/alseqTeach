<?php

declare(strict_types=1);

namespace App\Controller\Api;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/config')]
final class ConfigController
{
    public function __construct(
        private int $lessonPriceCentavos,
        private int $lessonDurationMinutes,
        private int $bookingHoldMinutes,
        private string $appTimezone,
    ) {
    }

    #[Route('', methods: ['GET'])]
    public function show(): JsonResponse
    {
        return new JsonResponse([
            'priceCentavos' => $this->lessonPriceCentavos,
            'currency' => 'BRL',
            'lessonDurationMinutes' => $this->lessonDurationMinutes,
            'holdMinutes' => $this->bookingHoldMinutes,
            'timezone' => $this->appTimezone,
        ]);
    }
}
