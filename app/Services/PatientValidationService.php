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
        return URL::temporarySignedRoute(
            'public.services',
            now()->addDays(7),
            [
                'tenant' => $tenant->slug,
                'documento' => $patient->documento,
                'phone' => $phone,
            ]
        );
    }

    public function generateRegisterUrl(Tenant $tenant, string $documento, string $phone): string
    {
        return URL::temporarySignedRoute(
            'public.patient.register',
            now()->addDays(7),
            [
                'tenant' => $tenant->slug,
                'documento' => $documento,
                'phone' => $phone,
            ]
        );
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