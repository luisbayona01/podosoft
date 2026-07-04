<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class ShortLink extends Model
{
    protected $fillable = [
        'code',
        'tenant_id',
        'original_url',
        'expires_at',
        'click_count',
        'last_access_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'last_access_at' => 'datetime',
    ];

    public function scopeValid(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function recordClick(): void
    {
        $this->increment('click_count');
        $this->update(['last_access_at' => now()]);
    }

    public static function generateCode(int $length = 8): string
    {
        $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $code = '';

        do {
            $code = '';
            for ($i = 0; $i < $length; $i++) {
                $code .= $characters[random_int(0, strlen($characters) - 1)];
            }
        } while (self::where('code', $code)->exists());

        return $code;
    }

    public function getShortUrl(): string
    {
        return config('app.url') . '/r/' . $this->code;
    }
}
