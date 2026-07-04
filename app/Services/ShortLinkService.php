<?php

namespace App\Services;

use App\Models\ShortLink;
use Illuminate\Support\Facades\Log;

class ShortLinkService
{
    public function create(string $originalUrl, ?int $tenantId = null, ?\DateTimeInterface $expiresAt = null, int $codeLength = 8): ShortLink
    {
        if ($expiresAt === null) {
            $expiresAt = now()->addDays(7);
        }

        $code = ShortLink::generateCode($codeLength);

        $shortLink = ShortLink::create([
            'code' => $code,
            'tenant_id' => $tenantId,
            'original_url' => $originalUrl,
            'expires_at' => $expiresAt,
        ]);

        Log::info('[DEBUG-SIGNATURE] ShortLinkService::create - SHORT LINK CREADO', [
            'code' => $code,
            'original_url' => $originalUrl,
            'original_url_length' => strlen($originalUrl),
            'expires_at' => $expiresAt->toIso8601String(),
            'expires_at_timestamp' => $expiresAt->timestamp,
            'short_url' => $shortLink->getShortUrl(),
            'tenant_id' => $tenantId,
        ]);

        return $shortLink;
    }

    public function findValidByCode(string $code): ?ShortLink
    {
        $shortLink = ShortLink::where('code', $code)->valid()->first();

        if ($shortLink) {
            $shortLink->recordClick();

            Log::info('[DEBUG-SIGNATURE] ShortLinkService::findValidByCode - SHORT LINK RECUPERADO', [
                'code' => $code,
                'original_url' => $shortLink->original_url,
                'original_url_length' => strlen($shortLink->original_url),
                'expires_at' => $shortLink->expires_at?->toIso8601String(),
                'expires_at_timestamp' => $shortLink->expires_at?->timestamp,
                'is_expired' => $shortLink->isExpired(),
                'was_modified' => false,
            ]);
        } else {
            Log::info('[DEBUG-SIGNATURE] ShortLinkService::findValidByCode - NO ENCONTRADO', [
                'code' => $code,
            ]);
        }

        return $shortLink;
    }

    public function getOrCreate(string $originalUrl, ?int $tenantId = null, int $expiresInDays = 7): ShortLink
    {
        $existing = ShortLink::where('original_url', $originalUrl)
            ->where('tenant_id', $tenantId)
            ->valid()
            ->first();

        if ($existing) {
            Log::info('[DEBUG-SIGNATURE] ShortLinkService::getOrCreate - REUTILIZADO EXISTENTE', [
                'original_url' => $originalUrl,
                'code' => $existing->code,
                'short_url' => $existing->getShortUrl(),
            ]);
            return $existing;
        }

        Log::info('[DEBUG-SIGNATURE] ShortLinkService::getOrCreate - CREANDO NUEVO', [
            'original_url' => $originalUrl,
        ]);

        return $this->create($originalUrl, $tenantId, now()->addDays($expiresInDays));
    }

    public function invalidateExpired(): int
    {
        $count = ShortLink::where('expires_at', '<', now())->delete();

        Log::info('[DEBUG-SIGNATURE] ShortLinkService::invalidateExpired - Invalidated expired short links', [
            'count' => $count,
        ]);

        return $count;
    }

    public function getUrl(string $originalUrl, ?int $tenantId = null): string
    {
        $shortLink = $this->getOrCreate($originalUrl, $tenantId);

        Log::info('[DEBUG-SIGNATURE] ShortLinkService::getUrl - URL CORTA GENERADA', [
            'original_url' => $originalUrl,
            'short_url' => $shortLink->getShortUrl(),
            'code' => $shortLink->code,
        ]);

        return $shortLink->getShortUrl();
    }
}
