<?php

namespace App\Services;

use App\Models\BotBlockedContact;

/**
 * Reglas de descarte del bot, evaluadas en Laravel ANTES de enviar el
 * mensaje al servicio de IA (Python):
 *
 *  - isGroup():       mensajes desde grupos (@g.us) se ignoran siempre.
 *  - blockedReason(): retorna la razón por la que un mensaje debe
 *                     descartarse, o null si debe continuar el flujo.
 *
 * Python nunca recibe mensajes de grupos ni de números bloqueados.
 */
class BotMessageGuardService
{
    /**
     * El remoteJid pertenece a un grupo de WhatsApp.
     */
    public function isGroup(?string $remoteJid): bool
    {
        return $remoteJid !== null && str_ends_with($remoteJid, '@g.us');
    }

    /**
     * Extrae y normaliza el teléfono del remoteJid (solo dígitos).
     */
    public function phoneFromJid(?string $remoteJid): ?string
    {
        return BotBlockedContact::normalizePhone($remoteJid);
    }

    /**
     * Devuelve la razón de descarte o null si el mensaje puede continuar.
     */
    public function blockedReason(?string $remoteJid): ?string
    {
        if ($remoteJid === null || $remoteJid === '') {
            return 'remoteJid ausente';
        }

        if ($this->isGroup($remoteJid)) {
            return 'mensaje de grupo';
        }

        if (BotBlockedContact::isBlocked($remoteJid)) {
            return 'número bloqueado';
        }

        return null;
    }
}
