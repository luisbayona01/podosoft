<?php

namespace App\Services;

interface AIServiceInterface
{
    public function analyzeMessage(string $message, array $context = [], array $history = []): array;

    public function composeFromSystemResult(string $systemInfo, array $context = [], array $history = []): array;
}
