<?php

declare(strict_types=1);

namespace App\Service;

use App\Exception\DomainException;

final class AdminAuth
{
    public function __construct(
        private string $adminPassword,
        private string $appSecret,
    ) {
    }

    public function login(string $password): string
    {
        if ($this->adminPassword === '' || !hash_equals($this->adminPassword, $password)) {
            throw new DomainException('Senha inválida.', 401, 'unauthorized');
        }

        return $this->token();
    }

    public function isValid(?string $token): bool
    {
        return is_string($token)
            && $token !== ''
            && $this->adminPassword !== ''
            && hash_equals($this->token(), $token);
    }

    public function token(): string
    {
        return hash_hmac('sha256', 'alseq-admin-v1', $this->appSecret."\0".$this->adminPassword);
    }
}
