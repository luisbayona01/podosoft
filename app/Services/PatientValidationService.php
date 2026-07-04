<?php

namespace App\Services;

use App\Models\Paciente;
use App\Models\Tenant;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Log;

class PatientValidationService
{
    public function validate(string $documento, int $tenantId): array
    {
        $patient = Paciente::where('tenant_id', $tenantId)
            ->where('documento', $documento)
            ->first();

        if (!$patient) {
            return [
                'exists' => false,
                'patient' => null,
                'message' => null,
                'redirect_url' => null,
            ];
        }

        return [
            'exists' => true,
            'patient' => $patient,
            'message' => "¡Hola {$patient->nombre}! Hemos identificado tu registro. Para continuar con tu solicitud, ingresa al siguiente enlace:",
            'redirect_url' => null,
        ];
    }

    public function generateServicesUrl(Tenant $tenant, Paciente $patient, string $phone): string
    {
        $expiresAt = now()->addDays(7);
        $params = [
            'tenant' => $tenant->slug,
            'documento' => $patient->documento,
            'phone' => $phone,
        ];

        Log::channel('single')->info('[DEBUG-SIGNATURE] generateServicesUrl - INICIO', [
            'app_url' => config('app.url'),
            'app_key' => config('app.key'),
            'now' => now()->toIso8601String(),
            'now_timestamp' => now()->timestamp,
            'expires_timestamp' => $expiresAt->timestamp,
            'expires_iso' => $expiresAt->toIso8601String(),
            'params' => $params,
        ]);

        $url = URL::temporarySignedRoute(
            'public.services',
            $expiresAt,
            $params
        );

        $parsed = parse_url($url);
        parse_str($parsed['query'] ?? '', $queryParams);

        Log::channel('single')->info('[DEBUG-SIGNATURE] generateServicesUrl - URL GENERADA', [
            'full_url' => $url,
            'scheme' => $parsed['scheme'] ?? null,
            'host' => $parsed['host'] ?? null,
            'path' => $parsed['path'] ?? null,
            'query_string' => $parsed['query'] ?? null,
            'query_params' => $queryParams,
            'signature' => $queryParams['signature'] ?? null,
            'expires_param' => $queryParams['expires'] ?? null,
        ]);

        return $url;
    }

    public function generateRegisterUrl(Tenant $tenant, string $documento, string $phone, $expiresAt = null): string
    {
        $expiresAt ??= now()->addDays(7);
        $params = [
            'tenant' => $tenant->slug,
            'documento' => $documento,
            'phone' => $phone,
        ];

        Log::channel('single')->info('[DEBUG-SIGNATURE] generateRegisterUrl - INICIO', [
            'app_url' => config('app.url'),
            'app_key' => config('app.key'),
            'now' => now()->toIso8601String(),
            'now_timestamp' => now()->timestamp,
            'expires_timestamp' => $expiresAt->timestamp,
            'expires_iso' => $expiresAt->toIso8601String(),
            'params' => $params,
        ]);

        $url = URL::temporarySignedRoute(
            'public.patient.register',
            $expiresAt,
            $params
        );

        $parsed = parse_url($url);
        parse_str($parsed['query'] ?? '', $queryParams);

        Log::channel('single')->info('[DEBUG-SIGNATURE] generateRegisterUrl - URL GENERADA', [
            'full_url' => $url,
            'scheme' => $parsed['scheme'] ?? null,
            'host' => $parsed['host'] ?? null,
            'path' => $parsed['path'] ?? null,
            'query_string' => $parsed['query'] ?? null,
            'query_params' => $queryParams,
            'signature' => $queryParams['signature'] ?? null,
            'expires_param' => $queryParams['expires'] ?? null,
        ]);

        return $url;
    }

    public function extractDocumentFromMessage(string $message): ?string
    {
        if (preg_match('/\b(\d{5,15})\b/', $message, $matches)) {
            return $matches[1];
        }
        return null;
    }

    public function extractDocumentTypeFromMessage(string $message): ?string
    {
        $types = ['CC', 'TI', 'CE', 'PA', 'PASAPORTE', 'CÉDULA'];

        foreach ($types as $type) {
            if (stripos($message, $type) !== false) {
                return strtoupper($type) === 'CÉDULA' ? 'CC' : strtoupper($type);
            }
        }

        return null;
    }

    public function findOrCreatePatientByPhone(string $phone, int $tenantId): ?Paciente
    {
        return Paciente::where('tenant_id', $tenantId)
            ->where('telefono', $phone)
            ->first();
    }

    public function associatePhoneIfNeeded(Paciente $patient, string $phone): void
    {
        if (empty($patient->telefono)) {
            $patient->update(['telefono' => $phone]);
        }
    }
}
