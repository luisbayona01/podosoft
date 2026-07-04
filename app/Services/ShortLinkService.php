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

        Log::info('[ShortLinkService] Short link created', [
            'code' => $code,
            'original_url' => $originalUrl,
            'expires_at' => $expiresAt,
        ]);

        return $shortLink;
    }

    public function findValidByCode(string $code): ?ShortLink
    {
        $shortLink = ShortLink::where('code', $code)->valid()->first();

        if ($shortLink) {
            $shortLink->recordClick();

            Log::info('[ShortLinkService] Short link accessed', [
                'code' => $code,
                'click_count' => $shortLink->click_count,
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
            return $existing;
        }

        return $this->create($originalUrl, $tenantId, now()->addDays($expiresInDays));
    }

    public function invalidateExpired(): int
    {
        $count = ShortLink::where('expires_at', '<', now())->delete();

        Log::info('[ShortLinkService] Invalidated expired short links', [
            'count' => $count,
        ]);

        return $count;
    }

    public function getUrl(string $originalUrl, ?int $tenantId = null): string
    {
        $shortLink = $this->getOrCreate($originalUrl, $tenantId);
        return $shortLink->getShortUrl();
    }
}