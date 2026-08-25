<?php

namespace App\Services;

use App\Models\TenantWhatsAppAccount;
use Illuminate\Support\Facades\Cache;

class WhatsAppContactsService
{
    public function __construct(
        protected WhatsAppContactNormalizer $normalizer,
    ) {
    }

    public function accountFor(int $tenantId): ?TenantWhatsAppAccount
    {
        return TenantWhatsAppAccount::query()
            ->where('tenant_id', $tenantId)
            ->where('provider', 'evolution')
            ->whereNull('deleted_at')
            ->orderByRaw("CASE WHEN status = 'connected' THEN 0 ELSE 1 END")
            ->latest('id')
            ->first();
    }

    public function validateAccountFor(int $tenantId): array
    {
        $account = $this->accountFor($tenantId);

        if (!$account) {
            return [
                'ok' => false,
                'error' => 'No se encontró una cuenta de WhatsApp configurada para esta clínica.',
            ];
        }

        if ($account->provider !== 'evolution') {
            return [
                'ok' => false,
                'error' => 'El proveedor de WhatsApp no es compatible.',
            ];
        }

        if (empty($account->instance_name) || empty($account->server_url) || empty($account->api_key)) {
            return [
                'ok' => false,
                'error' => 'La configuración de la cuenta de WhatsApp está incompleta.',
            ];
        }

        if ($account->status !== 'connected' && !$this->isReallyConnected($account)) {
            return [
                'ok' => false,
                'error' => 'El dispositivo de WhatsApp no está conectado. Conecta tu cuenta antes de consultar los contactos.',
            ];
        }

        if ($account->status !== 'connected') {
            $account->markAsConnected($account->phone);
        }

        return [
            'ok' => true,
            'account' => $account,
        ];
    }

    public function fetchFor(int $tenantId, bool $forceRefresh = false): array
    {
        $validated = $this->validateAccountFor($tenantId);

        if (!$validated['ok']) {
            return [
                'ok' => false,
                'error' => $validated['error'],
            ];
        }

        $account = $validated['account'];

        $cacheKey = $this->cacheKey($account);

        if (!$forceRefresh && Cache::has($cacheKey)) {
            $cached = Cache::get($cacheKey);
            $cached['account'] = $this->accountSafeFields($account);
            $cached['from_cache'] = true;

            return $cached;
        }

        $evolution = app(EvolutionApiService::class)->fromAccount($account);

        $pages = 0;

        $result = $evolution->getAllContacts($account, function (int $page, int $total) use (&$pages) {
            $pages = $page;
        });

        if (!$result['success']) {
            return [
                'ok' => false,
                'error' => $result['error'] ?? 'No fue posible conectarse con WhatsApp.',
                'http_status' => $result['http_status'] ?? null,
            ];
        }

        $normalized = [];

        foreach ($result['contacts'] as $raw) {
            $normalized[] = $this->normalizer->normalize($raw);
        }

        $data = [
            'ok' => true,
            'account' => $this->accountSafeFields($account),
            'contacts' => $normalized,
            'pages' => $result['pages'],
            'duplicates' => $result['duplicates'],
            'summary' => $this->summarize($normalized),
            'fetched_at' => now()->toIso8601String(),
            'from_cache' => false,
        ];

        Cache::put($cacheKey, $data, now()->addMinutes((int) config('whatsapp-contacts.cache_ttl_minutes', 10)));

        $account->update(['last_seen_at' => now()]);

        return $data;
    }

    /**
     * Obtiene UNA página de contactos y la acumula en caché.
     * Permite cargar miles de contactos progresivamente sin timeout (504).
     */
    public function fetchPageFor(int $tenantId, bool $forceRefresh = false): array
    {
        $validated = $this->validateAccountFor($tenantId);

        if (!$validated['ok']) {
            return [
                'ok' => false,
                'error' => $validated['error'],
            ];
        }

        $account = $validated['account'];
        $cacheKey = $this->cacheKey($account);
        $ttl = now()->addMinutes((int) config('whatsapp-contacts.cache_ttl_minutes', 10));

        $data = $forceRefresh ? null : Cache::get($cacheKey);

        // Dataset ya completo en caché
        if ($data && ($data['completed'] ?? false)) {
            $data['account'] = $this->accountSafeFields($account);
            $data['from_cache'] = true;
            $data['ok'] = true;

            return $data;
        }

        if (!$data) {
            $data = [
                'contacts' => [],
                'seen' => [],
                'pages' => 0,
                'duplicates' => 0,
            ];
        }

        $take = (int) config('whatsapp-contacts.pagination_size', 100);
        $maxPages = (int) config('whatsapp-contacts.max_pages', 250);
        $skip = $data['pages'] * $take;

        $evolution = app(EvolutionApiService::class)->fromAccount($account);
        $result = $evolution->findContacts($account->instance_name, $take, $skip, [], ['id' => 'asc']);

        if (!$result['success']) {
            return [
                'ok' => false,
                'error' => $result['error'] ?? 'No fue posible conectarse con WhatsApp.',
                'http_status' => $result['http_status'] ?? null,
                'contacts' => $data['contacts'],
                'summary' => $this->summarize($data['contacts']),
                'pages' => $data['pages'],
                'duplicates' => $data['duplicates'],
                'has_more' => false,
            ];
        }

        $batch = $result['contacts'];

        foreach ($batch as $raw) {
            if (!is_array($raw)) {
                continue;
            }

            $key = $raw['remoteJid'] ?? $raw['id'] ?? $raw['number'] ?? null;

            if ($key === null || $key === '') {
                $key = 'raw:' . md5(serialize($raw));
            }

            if (isset($data['seen'][$key])) {
                $data['duplicates']++;
                continue;
            }

            $data['seen'][$key] = true;
            $data['contacts'][] = $this->normalizer->normalize($raw);
        }

        $data['pages']++;
        $hasMore = count($batch) >= $take && $data['pages'] < $maxPages;

        $payload = [
            'ok' => true,
            'account' => $this->accountSafeFields($account),
            'contacts' => $data['contacts'],
            'pages' => $data['pages'],
            'duplicates' => $data['duplicates'],
            'summary' => $this->summarize($data['contacts']),
            'fetched_at' => now()->toIso8601String(),
            'from_cache' => false,
            'has_more' => $hasMore,
            'completed' => !$hasMore,
            'seen' => $data['seen'],
        ];

        Cache::put($cacheKey, $payload, $ttl);

        $account->update(['last_seen_at' => now()]);

        return $payload;
    }

    public function summarize(array $contacts): array
    {
        $personal = 0;
        $groups = 0;
        $invalid = 0;
        $others = 0;

        foreach ($contacts as $contact) {
            match ($contact['class'] ?? 'invalid') {
                'personal' => $personal++,
                'group' => $groups++,
                'invalid' => $invalid++,
                default => $others++,
            };
        }

        return [
            'found' => count($contacts),
            'personal' => $personal,
            'groups' => $groups,
            'invalid' => $invalid,
            'others' => $others,
        ];
    }

    public function accountSafeFields(TenantWhatsAppAccount $account): array
    {
        return [
            'instance_name' => $account->instance_name,
            'phone' => $account->phone,
            'status' => $account->status,
            'server_url' => $account->server_url,
        ];
    }

    public function buildGeneralCsv(array $contacts): string
    {
        $header = ['nombre', 'apellido', 'telefono', 'push_name', 'remote_jid', 'tipo'];

        $lines = [implode(',', $header)];

        foreach ($contacts as $contact) {
            $lines[] = implode(',', [
                $this->csvCell($contact['name'] ?? ''),
                '',
                $this->csvCell($contact['phone'] ?? ''),
                $this->csvCell($contact['push_name'] ?? ''),
                $this->csvCell($contact['remote_jid'] ?? ''),
                $this->csvCell($contact['type'] ?? ''),
            ]);
        }

        return implode("\n", $lines) . "\n";
    }

    public function buildPatientsCsv(array $contacts): string
    {
        $header = config('patient-import.columns');

        $lines = [implode(',', $header)];

        foreach ($contacts as $contact) {
            if (($contact['class'] ?? null) !== 'personal') {
                continue;
            }

            [$nombre, $apellido] = $this->splitName($contact['name'] ?? '');

            $values = [
                $nombre,
                $apellido,
                $contact['phone'] ?? '',
                '',
                '',
                '',
                '',
                '',
                '',
            ];

            $lines[] = implode(',', $values);
        }

        return implode("\n", $lines) . "\n";
    }

    protected function splitName(string $name): array
    {
        $name = trim($name);

        if ($name === '') {
            return ['', ''];
        }

        $parts = preg_split('/\s+/', $name);

        if (count($parts) === 1) {
            return [$parts[0], ''];
        }

        $nombre = array_shift($parts);

        return [$nombre, implode(' ', $parts)];
    }

    protected function csvCell(mixed $value): string
    {
        $value = is_scalar($value) ? (string) $value : '';

        if (str_contains($value, ',') || str_contains($value, '"') || str_contains($value, "\n")) {
            return '"' . str_replace('"', '""', $value) . '"';
        }

        return $value;
    }

    protected function isReallyConnected(TenantWhatsAppAccount $account): bool
    {
        $evolution = app(EvolutionApiService::class)->fromAccount($account);
        $result = $evolution->getConnectionInfo($account->instance_name);

        return $result['success'] && ($result['status'] ?? 'unknown') === 'open';
    }

    protected function cacheKey(TenantWhatsAppAccount $account): string
    {
        return "whatsapp_contacts_{$account->tenant_id}_{$account->id}";
    }
}