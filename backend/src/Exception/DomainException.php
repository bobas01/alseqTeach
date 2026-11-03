<?php

declare(strict_types=1);

namespace App\Exception;

final class DomainException extends \RuntimeException
{
    /**
     * @param array<string, string> $fields
     */
    public function __construct(
        string $message,
        public readonly int $statusCode = 400,
        public readonly string $errorCode = 'domain_error',
        public readonly array $fields = [],
    ) {
        parent::__construct($message);
    }
}
