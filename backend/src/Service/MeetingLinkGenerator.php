<?php

declare(strict_types=1);

namespace App\Service;

final class MeetingLinkGenerator
{
    public function __construct(private string $jitsiBaseUrl)
    {
    }

    public function createToken(): string
    {
        return bin2hex(random_bytes(16));
    }

    public function urlFor(string $token): string
    {
        return rtrim($this->jitsiBaseUrl, '/').'/alseqteach-'.$token;
    }
}
