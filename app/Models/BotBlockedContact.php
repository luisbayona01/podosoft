<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BotBlockedContact extends Model
{
    protected $fillable = [
        'phone',
        'reason',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    /**
     * Normaliza un teléfono o remoteJid a solo dígitos.
     *
     * "573001234567@s.whatsapp.net" -> "573001234567"
     * "+57 300 123 4567"            -> "573001234567"
     */
    public static function normalizePhone(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        // Quitar la parte del dominio de un remoteJid (todo lo que sigue a "@").
        $value = explode('@', $value)[0];

        $digits = preg_replace('/\D/', '', $value);

        return $digits === '' ? null : $digits;
    }

    /**
     * ¿Este teléfono/remoteJid tiene un bloqueo activo?
     */
    public static function isBlocked(?string $phoneOrJid): bool
    {
        $normalized = self::normalizePhone($phoneOrJid);

        if ($normalized === null) {
            return false;
        }

        return static::where('phone', $normalized)->where('active', true)->exists();
    }
}
