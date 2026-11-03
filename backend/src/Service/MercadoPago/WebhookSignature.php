<?php

declare(strict_types=1);

namespace App\Service\MercadoPago;

use Symfony\Component\HttpFoundation\Request;

final class WebhookSignature
{
    public function __construct(
        private string $mercadoPagoWebhookSecret,
        private string $kernelEnvironment,
    ) {
    }

    public function assertValid(Request $request): void
    {
        if ($this->mercadoPagoWebhookSecret === '') {
            if ($this->kernelEnvironment === 'prod') {
                throw new \App\Exception\DomainException('Webhook sem segredo configurado.', 500, 'webhook_misconfigured');
            }

            return;
        }

        $header = $request->headers->get('x-signature', '');
        if (!preg_match('/(?:^|,)ts=(\d+)/', $header, $ts) || !preg_match('/(?:^|,)v1=([a-f0-9]+)/', $header, $hash)) {
            throw new \App\Exception\DomainException('Assinatura do webhook ausente.', 401, 'invalid_signature');
        }

        $dataId = strtolower($this->dataId($request));
        $manifest = sprintf(
            'id:%s;request-id:%s;ts:%s;',
            $dataId,
            $request->headers->get('x-request-id', ''),
            $ts[1],
        );
        $expected = hash_hmac('sha256', $manifest, $this->mercadoPagoWebhookSecret);
        if (!hash_equals($expected, $hash[1])) {
            throw new \App\Exception\DomainException('Assinatura do webhook inválida.', 401, 'invalid_signature');
        }
    }

    private function dataId(Request $request): string
    {
        $fromQuery = $request->query->get('data_id');
        if (is_scalar($fromQuery) && (string) $fromQuery !== '') {
            return (string) $fromQuery;
        }

        $query = (string) $request->server->get('QUERY_STRING', '');
        if (preg_match('/(?:^|&)data\.id=([^&]+)/', $query, $match) === 1) {
            return urldecode($match[1]);
        }

        $decoded = json_decode($request->getContent(), true);
        if (is_array($decoded) && isset($decoded['data']['id']) && is_scalar($decoded['data']['id'])) {
            return (string) $decoded['data']['id'];
        }

        return '';
    }
}
