<?php

namespace App\Services;

class WhatsAppContactNormalizer
{
    public function normalize(array $raw): array
    {
        $remoteJid = (string) ($raw['remoteJid'] ?? $raw['remote_jid'] ?? $raw['id'] ?? '');
        $pushName = (string) ($raw['pushName'] ?? $raw['push_name'] ?? '');
        $name = (string) ($raw['name'] ?? '');
        $name = $name !== '' ? $name : $pushName;
        $numberField = (string) ($raw['number'] ?? '');
        $profilePicture = (string) ($raw['profilePicUrl'] ?? $raw['profile_picture_url'] ?? $raw['profilePictureUrl'] ?? '');
        $isGroup = $this->toBool($raw['isGroup'] ?? $raw['is_group'] ?? false);
        $isSaved = $this->toBool($raw['isSaved'] ?? $raw['is_saved'] ?? false);
        $type = (string) ($raw['type'] ?? $raw['tipo'] ?? '');

        $className = $this->classify($remoteJid, $numberField, $isGroup, $type);

        if ($type === '') {
            $type = $className;
        }

        return [
            'name' => $name,
            'push_name' => $pushName,
            'phone' => $this->extractPhone($remoteJid, $numberField),
            'remote_jid' => $remoteJid,
            'type' => $type,
            'class' => $className,
            'is_group' => $isGroup,
            'is_saved' => $isSaved,
            'profile_picture_url' => $profilePicture,
            'raw' => $raw,
        ];
    }

    protected function classify(string $remoteJid, string $number, bool $isGroup, string $type): string
    {
        if ($type !== '' && str_contains(strtolower($type), 'group')) {
            return 'group';
        }

        if ($isGroup || str_ends_with($remoteJid, '@g.us')) {
            return 'group';
        }

        if (str_ends_with($remoteJid, '@broadcast')) {
            return 'broadcast';
        }

        if (str_ends_with($remoteJid, '@newsletter') || str_contains(strtolower($type), 'newsletter')) {
            return 'newsletter';
        }

        if (str_ends_with($remoteJid, '@lid')) {
            return 'device';
        }

        if (str_ends_with($remoteJid, '@s.whatsapp.net')) {
            $prefix = explode('@', $remoteJid)[0];

            if (preg_match('/^\d+$/', $prefix) === 1) {
                return 'personal';
            }

            return 'invalid';
        }

        if ($remoteJid === '' && $number === '') {
            return 'invalid';
        }

        return 'invalid';
    }

    protected function extractPhone(string $remoteJid, string $number): ?string
    {
        if ($number !== '') {
            return app(PhoneNormalizerService::class)->normalize($number);
        }

        if ($remoteJid === '') {
            return null;
        }

        $digits = preg_replace('/[^0-9]/', '', explode('@', $remoteJid)[0]);

        if ($digits === '') {
            return null;
        }

        return $digits;
    }

    protected function toBool(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_string($value)) {
            return in_array(strtolower($value), ['true', '1', 'yes'], true);
        }

        return (bool) $value;
    }
}