<?php

declare(strict_types=1);

namespace App\Http;

use App\Exception\DomainException;
use Symfony\Component\HttpFoundation\Request;

final class DateRangeParser
{
    public function __construct(private string $appTimezone)
    {
    }

    /**
     * @return array{0: \DateTimeImmutable, 1: \DateTimeImmutable}
     */
    public function parse(Request $request, int $defaultDays = 21): array
    {
        $timezone = new \DateTimeZone($this->appTimezone);
        $today = new \DateTimeImmutable('today', $timezone);
        $from = $this->day($request->query->get('from'), $today, $timezone);
        $to = $this->day($request->query->get('to'), $today->modify(sprintf('+%d days', $defaultDays)), $timezone);
        if ($to <= $from) {
            throw new DomainException('O intervalo de datas é inválido.', 400, 'invalid_range');
        }
        if ($from->diff($to)->days > 62) {
            throw new DomainException('O intervalo máximo é de 62 dias.', 400, 'invalid_range');
        }

        return [$from, $to];
    }

    private function day(mixed $value, \DateTimeImmutable $fallback, \DateTimeZone $timezone): \DateTimeImmutable
    {
        if ($value === null || $value === '') {
            return $fallback;
        }
        if (!is_string($value) || preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) !== 1) {
            throw new DomainException('Use datas no formato AAAA-MM-DD.', 400, 'invalid_range');
        }
        $day = \DateTimeImmutable::createFromFormat('!Y-m-d', $value, $timezone);
        if (!$day || $day->format('Y-m-d') !== $value) {
            throw new DomainException('Use datas no formato AAAA-MM-DD.', 400, 'invalid_range');
        }

        return $day;
    }
}
