<?php

declare(strict_types=1);

namespace App\Service;

final class Cpf
{
    public static function normalize(string $value): string
    {
        return preg_replace('/\D+/', '', $value) ?? '';
    }

    public static function isValid(string $value): bool
    {
        $digits = self::normalize($value);
        if (strlen($digits) !== 11 || preg_match('/^(\d)\1{10}$/', $digits) === 1) {
            return false;
        }

        for ($length = 9; $length <= 10; $length++) {
            $sum = 0;
            for ($i = 0; $i < $length; $i++) {
                $sum += (int) $digits[$i] * (($length + 1) - $i);
            }
            $remainder = $sum % 11;
            $expected = $remainder < 2 ? 0 : 11 - $remainder;
            if ((int) $digits[$length] !== $expected) {
                return false;
            }
        }

        return true;
    }
}
