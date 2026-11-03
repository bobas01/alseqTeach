<?php

declare(strict_types=1);

namespace App\Service\MercadoPago;

use App\Entity\Booking;
use App\Exception\DomainException;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class MercadoPagoClient
{
    public function __construct(
        #[Autowire(service: 'mercadopago.client')]
        private HttpClientInterface $http,
        private LoggerInterface $logger,
        private string $mercadoPagoAccessToken,
        private string $mercadoPagoMode,
        private string $appPublicUrl,
        private string $kernelEnvironment,
    ) {
    }

    public function isStub(): bool
    {
        return $this->mercadoPagoMode === 'stub';
    }

    /**
     * @return array{id: string, status: string, copyPaste: ?string, qrCodeBase64: ?string, ticketUrl: ?string}
     */
    public function createPixPayment(Booking $booking): array
    {
        $this->assertOperational();

        if ($this->isStub()) {
            return [
                'id' => 'stub-'.$booking->getId(),
                'status' => 'pending',
                'copyPaste' => '00020126580014BR.GOV.BCB.PIX0136stub-'.$booking->getId(),
                'qrCodeBase64' => null,
                'ticketUrl' => null,
            ];
        }

        $payload = [
            'transaction_amount' => 0,
            'description' => 'Aula particular de francês',
            'payment_method_id' => 'pix',
            'external_reference' => $booking->getId(),
            'payer' => [
                'email' => $booking->getStudentEmail(),
                'first_name' => $this->firstName($booking->getStudentName()),
                'last_name' => $this->lastName($booking->getStudentName()),
                'identification' => [
                    'type' => 'CPF',
                    'number' => $booking->getStudentCpf(),
                ],
            ],
        ];

        if (str_starts_with($this->appPublicUrl, 'https://')) {
            $payload['notification_url'] = rtrim($this->appPublicUrl, '/').'/api/webhooks/mercadopago';
        }

        $amount = sprintf('%d.%02d', intdiv($booking->getAmountCentavos(), 100), $booking->getAmountCentavos() % 100);
        $json = json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        $count = 0;
        $json = preg_replace('/"transaction_amount":0([,}])/', '"transaction_amount":'.$amount.'$1', $json, 1, $count);
        if ($json === null || $count !== 1) {
            throw new DomainException('Não foi possível montar a cobrança Pix.', 500, 'payment_provider_error');
        }

        $response = $this->http->request('POST', '/v1/payments', [
            'headers' => [
                'Authorization' => 'Bearer '.$this->mercadoPagoAccessToken,
                'X-Idempotency-Key' => $booking->getId(),
                'Content-Type' => 'application/json',
            ],
            'body' => $json,
        ]);

        $status = $response->getStatusCode();
        $data = $response->toArray(false);
        if ($status >= 400) {
            $this->logger->error('Mercado Pago a refusé la création du Pix.', [
                'status' => $status,
                'message' => $data['message'] ?? null,
                'booking' => $booking->getId(),
            ]);
            throw new DomainException('O Mercado Pago não gerou o Pix. Tente de novo em instantes.', 502, 'payment_provider_error');
        }

        $transaction = $data['point_of_interaction']['transaction_data'] ?? [];
        $copyPaste = isset($transaction['qr_code']) ? (string) $transaction['qr_code'] : null;
        if ($copyPaste === null || $copyPaste === '') {
            throw new DomainException('O Mercado Pago não devolveu o código Pix.', 502, 'payment_provider_error');
        }

        return [
            'id' => (string) $data['id'],
            'status' => (string) ($data['status'] ?? 'pending'),
            'copyPaste' => $copyPaste,
            'qrCodeBase64' => isset($transaction['qr_code_base64']) ? (string) $transaction['qr_code_base64'] : null,
            'ticketUrl' => isset($transaction['ticket_url']) ? (string) $transaction['ticket_url'] : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function fetchPayment(string $paymentId): array
    {
        $this->assertOperational();
        if ($this->isStub()) {
            throw new DomainException('Webhook indisponível no modo stub.', 404, 'not_found');
        }

        $response = $this->http->request('GET', '/v1/payments/'.rawurlencode($paymentId), [
            'headers' => [
                'Authorization' => 'Bearer '.$this->mercadoPagoAccessToken,
            ],
        ]);

        $status = $response->getStatusCode();
        $data = $response->toArray(false);
        if ($status >= 400 || !isset($data['id'])) {
            $this->logger->error('Mercado Pago n’a pas renvoyé le paiement.', [
                'status' => $status,
                'payment' => $paymentId,
            ]);
            throw new DomainException('Não foi possível confirmar o pagamento.', 502, 'payment_provider_error');
        }

        return $data;
    }

    private function assertOperational(): void
    {
        if ($this->mercadoPagoMode === 'stub') {
            if ($this->kernelEnvironment === 'prod') {
                throw new DomainException('O modo stub do Mercado Pago não pode ser usado em produção.', 500, 'payment_misconfigured');
            }

            return;
        }

        if ($this->mercadoPagoMode !== 'api') {
            throw new DomainException('MERCADOPAGO_MODE inválido.', 500, 'payment_misconfigured');
        }

        if ($this->mercadoPagoAccessToken === '') {
            throw new DomainException('Configure MERCADOPAGO_ACCESS_TOKEN para gerar o Pix.', 503, 'payment_misconfigured');
        }
    }

    private function firstName(string $fullName): string
    {
        $parts = preg_split('/\s+/', trim($fullName)) ?: [];

        return $this->clip($parts[0] ?? 'Aluno');
    }

    private function lastName(string $fullName): string
    {
        $parts = preg_split('/\s+/', trim($fullName)) ?: [];
        if (count($parts) < 2) {
            return $this->clip($parts[0] ?? 'Aluno');
        }

        return $this->clip(implode(' ', array_slice($parts, 1)));
    }

    private function clip(string $value): string
    {
        return mb_substr($value, 0, 50);
    }
}
