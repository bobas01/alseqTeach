<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Service\BookingService;
use App\Service\MercadoPago\MercadoPagoClient;
use App\Service\MercadoPago\WebhookSignature;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class MercadoPagoWebhookController
{
    public function __construct(
        private WebhookSignature $signature,
        private MercadoPagoClient $mercadoPago,
        private BookingService $bookings,
        private LoggerInterface $logger,
    ) {
    }

    #[Route('/api/webhooks/mercadopago', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        $this->signature->assertValid($request);
        $body = $this->body($request);
        $type = $request->query->get('type') ?? $request->query->get('topic') ?? ($body['type'] ?? null);
        if (is_string($type) && !in_array($type, ['payment', 'payment.created', 'payment.updated'], true)) {
            return new JsonResponse(['received' => true]);
        }

        $paymentId = $this->paymentId($request, $body);
        if ($paymentId === null) {
            $this->logger->info('Webhook Mercado Pago sans identifiant de paiement.');

            return new JsonResponse(['received' => true]);
        }

        $payment = $this->mercadoPago->fetchPayment($paymentId);
        $this->bookings->applyProviderPayment($payment);

        return new JsonResponse(['received' => true]);
    }

    /**
     * @return array<string, mixed>
     */
    private function body(Request $request): array
    {
        $content = $request->getContent();
        if ($content === '') {
            return [];
        }
        $decoded = json_decode($content, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @param array<string, mixed> $body
     */
    private function paymentId(Request $request, array $body): ?string
    {
        $candidates = [
            $request->query->get('data_id'),
            $request->query->get('id'),
            $body['data']['id'] ?? null,
        ];
        foreach ($candidates as $candidate) {
            if (is_scalar($candidate) && (string) $candidate !== '') {
                return (string) $candidate;
            }
        }

        return null;
    }
}
