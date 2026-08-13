<?php

namespace App\Services;

use App\Models\Paciente;

class PatientDuplicateFinder
{
    protected int $tenantId = 0;

    protected array $byDocument = [];

    protected array $byPhone = [];

    public function forTenant(int $tenantId): self
    {
        $this->tenantId = $tenantId;

        return $this;
    }

    public function load(array $rows): self
    {
        $documents = [];

        $phones = [];

        foreach ($rows as $row) {
            $documento = $row['documento'] ?? null;
            $telefono = $row['telefono'] ?? null;

            if ($documento !== null && trim((string) $documento) !== '') {
                $documents[trim((string) $documento)] = true;
            }

            if ($telefono !== null && trim((string) $telefono) !== '') {
                $phones[trim((string) $telefono)] = true;
            }
        }

        if (!empty($documents)) {
            Paciente::withTrashed()
                ->where('tenant_id', $this->tenantId)
                ->whereIn('documento', array_keys($documents))
                ->get(['id', 'tenant_id', 'documento', 'telefono', 'nombre', 'apellido'])
                ->each(function (Paciente $paciente) {
                    if ($paciente->documento !== null) {
                        $this->byDocument[$paciente->documento] = $paciente;
                    }
                });
        }

        if (!empty($phones)) {
            Paciente::withTrashed()
                ->where('tenant_id', $this->tenantId)
                ->whereIn('telefono', array_keys($phones))
                ->get(['id', 'tenant_id', 'documento', 'telefono', 'nombre', 'apellido'])
                ->each(function (Paciente $paciente) {
                    if ($paciente->telefono !== null) {
                        $this->byPhone[$paciente->telefono] = $paciente;
                    }
                });
        }

        return $this;
    }

    public function findByDocument(?string $documento): ?Paciente
    {
        if ($documento === null || trim($documento) === '') {
            return null;
        }

        return $this->byDocument[trim($documento)] ?? null;
    }

    public function findByPhone(?string $telefono): ?Paciente
    {
        if ($telefono === null || trim($telefono) === '') {
            return null;
        }

        return $this->byPhone[trim($telefono)] ?? null;
    }

    public function isDuplicate(array $row): ?string
    {
        $documento = $row['documento'] ?? null;
        $telefono = $row['telefono'] ?? null;

        if ($documento !== null && trim((string) $documento) !== '') {
            if ($this->findByDocument((string) $documento) !== null) {
                return 'documento';
            }
        }

        if ($telefono !== null && trim((string) $telefono) !== '') {
            if ($this->findByPhone((string) $telefono) !== null) {
                return 'telefono';
            }
        }

        return null;
    }
}