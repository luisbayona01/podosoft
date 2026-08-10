<?php

namespace App\Services;

use App\Models\HorarioProfesional;
use App\Models\Sede;
use App\Models\Tenant;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Pushes per-tenant knowledge to the Podosoft AI microservice (Python).
 *
 * Python keeps the tenant KB (name, horarios, direccion, servicios, sedes)
 * in Redis with a TTL. Every time a WhatsApp account becomes connected we
 * re-push the KB so the assistant always has fresh static data (services,
 * hours, locations) even if the Redis copy expired.
 *
 * Contract (POST {PYTHON_AI_URL}/internal/knowledge/sync):
 *   request:  {tenant_id, name, horarios, direccion,
 *              servicios: [{id,nombre,duracion,precio}], faq, politicas, promociones}
 *   response: 204 No Content
 */
class PodosoftKnowledgeSyncService
{
    private string $baseUrl;
    private ?string $token;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('services.python_ai.url'), '/');
        $this->token = config('services.python_ai.token') ?: null;
    }

    public function configured(): bool
    {
        return $this->baseUrl !== '';
    }

    public function buildPayload(int $tenantId): array
    {
        $tenant = Tenant::with(['sedes', 'servicios'])->find($tenantId);

        $horarios = $this->summarizeHorarios();

        return [
            'tenant_id' => $tenantId,
            'name' => $tenant->nombre ?? '',
            'slug' => $tenant->slug ?? '',
            'horarios' => $horarios,
            'direccion' => $tenant->direccion ?? '',
            'servicios' => ($tenant->servicios ?? collect())
                ->where('activo', true)
                ->map(fn ($s) => [
                    'id' => $s->id,
                    'nombre' => $s->nombre,
                    'duracion' => $s->duracion,
                    'precio' => $s->precio,
                ])
                ->values()
                ->toArray(),
            'sedes' => ($tenant->sedes ?? collect())
                ->map(fn ($sd) => [
                    'id' => $sd->id,
                    'nombre' => $sd->nombre,
                    'direccion' => $sd->direccion,
                ])
                ->values()
                ->toArray(),
            'faq' => [],
            'politicas' => [],
            'promociones' => [],
        ];
    }

    public function push(int $tenantId): bool
    {
        if (!$this->configured()) {
            return false;
        }

        $payload = $this->buildPayload($tenantId);

        try {
            $response = Http::timeout(10)
                ->acceptJson()
                ->asJson()
                ->when($this->token, fn ($h) => $h->withToken($this->token))
                ->post("{$this->baseUrl}/internal/knowledge/sync", $payload);

            if ($response->status() === 204) {
                Log::info('[KnowledgeSync] KB pushed to Python', [
                    'tenant_id' => $tenantId,
                    'servicios' => count($payload['servicios']),
                    'sedes' => count($payload['sedes']),
                ]);
                return true;
            }

            Log::warning('[KnowledgeSync] Non-204 from Python', [
                'tenant_id' => $tenantId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        } catch (\Exception $e) {
            Log::error('[KnowledgeSync] Failed to push KB', [
                'tenant_id' => $tenantId,
                'error' => $e->getMessage(),
            ]);
        }

        return false;
    }

    /**
     * Builds a human summary of weekly office hours from the professionals'
     * schedules, e.g. "Lunes a Viernes: 8:00 - 17:00".
     */
    private function summarizeHorarios(): string
    {
        $rows = HorarioProfesional::where('activo', true)
            ->orderBy('dia_semana')
            ->get(['dia_semana', 'hora_inicio', 'hora_fin']);

        if ($rows->isEmpty()) {
            return '';
        }

        $order = ['lunes' => 0, 'martes' => 1, 'miercoles' => 2, 'jueves' => 3, 'viernes' => 4, 'sabado' => 5, 'domingo' => 6];
        $labels = ['lunes' => 'Lunes', 'martes' => 'Martes', 'miercoles' => 'Miércoles', 'jueves' => 'Jueves', 'viernes' => 'Viernes', 'sabado' => 'Sábado', 'domingo' => 'Domingo'];

        $days = $rows
            ->unique('dia_semana')
            ->sortBy(fn ($r) => $order[$r->dia_semana] ?? 99)
            ->map(function ($r) use ($labels) {
                $start = substr((string) $r->hora_inicio, 0, 5);
                $end = substr((string) $r->hora_fin, 0, 5);
                return $labels[$r->dia_semana] . ': ' . $start . ' - ' . $end;
            })
            ->values()
            ->implode('; ');

        return $days;
    }
}