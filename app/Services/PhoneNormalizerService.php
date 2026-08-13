<?php

namespace App\Services;

class PhoneNormalizerService
{
    public function normalize(?string $phone): ?string
    {
        if ($phone === null || trim($phone) === '') {
            return null;
        }

        $phone = trim($phone);

        $hasPlus = str_starts_with($phone, '+');

        $digitsOnly = preg_replace('/[^0-9]/', '', $phone);

        if ($digitsOnly === '') {
            return null;
        }

        return ($hasPlus ? '+' : '') . $digitsOnly;
    }

    public function isValid(?string $phone): bool
    {
        $normalized = $this->normalize($phone);

        if ($normalized === null) {
            return false;
        }

        return preg_match('/^\+?[0-9]{7,15}$/', $normalized) === 1;
    }
}