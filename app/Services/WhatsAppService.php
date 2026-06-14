<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class WhatsAppService
{
    public function sendText(string $number, string $text): bool
    {
        \Illuminate\Support\Facades\Log::info('[AUDIT-4] Sending text to Evolution API', [
            'number' => $number,
            'text' => $text,
            'url' => config('services.evolution.url')
        ]);

        // Eliminar bytes nulos (\0) que causan errores en PostgreSQL (Evolution API)
        $text = str_replace("\0", "", $text);

        try {
            $response = Http::withHeaders([
                'apikey' => config('services.evolution.key'),
            ])->withOptions([
                'verify' => false,
            ])->post(config('services.evolution.url'), [
                'number' => $number,
                'text' => $text,
            ]);

            \Illuminate\Support\Facades\Log::info('[AUDIT-4] Evolution API response', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            if ($response->failed()) {
                Log::error("Evolution API Error: " . $response->body());
                return false;
            }

            return true;
        } catch (Exception $e) {
            Log::error("WhatsAppService Error: " . $e->getMessage());
            return false;
        }
    }
}
