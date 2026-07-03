<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Support\Facades\Cache;

class TenantResolverService
{
    private ?Tenant $tenant = null;
    private string $slug;

    public function __construct(string $slug)
    {
        $this->slug = $slug;
    }

    public function resolve(): ?Tenant
    {
        if ($this->tenant !== null) {
            return $this->tenant;
        }

        $this->tenant = Cache::remember(
            "tenant:{$this->slug}",
            3600,
            fn () => Tenant::where('slug', $this->slug)
                ->where('activo', true)
                ->first()
        );

        return $this->tenant;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public static function resolveById(int $id): ?Tenant
    {
        return Cache::remember(
            "tenant:id:{$id}",
            3600,
            fn () => Tenant::find($id)
        );
    }

    public static function clearCache(string $slug): void
    {
        Cache::forget("tenant:{$slug}");
    }

    public function getConfig(): array
    {
        $tenant = $this->resolve();

        if (!$tenant) {
            return [];
        }

        return [
            'id' => $tenant->id,
            'nombre' => $tenant->nombre,
            'slug' => $tenant->slug,
            'logo' => $tenant->logo,
            'telefono' => $tenant->telefono,
            'email' => $tenant->email,
            'direccion' => $tenant->direccion,
        ];
    }
}