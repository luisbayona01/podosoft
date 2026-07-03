<?php

namespace App\Livewire;

use App\Models\Servicio;
use App\Models\Tenant;
use App\Services\TenantResolverService;
use Illuminate\Support\Facades\URL;
use Livewire\Component;

class Services extends Component
{
    public string $tenantSlug;
    public array $tenantConfig = [];
    public array $servicios = [];
    public ?string $documento = null;
    public ?string $phone = null;

    public function mount(string $tenant): void
    {
        $this->tenantSlug = $tenant;
        $resolver = new TenantResolverService($tenant);
        $tenantModel = $resolver->resolve();

        if (!$tenantModel) {
            abort(404, 'Clínica no encontrada');
        }

        $this->tenantConfig = $resolver->getConfig();
        $this->documento = request()->get('documento');
        $this->phone = request()->get('phone');

        $this->loadServicios($tenantModel->id);
    }

    private function loadServicios(int $tenantId): void
    {
        $this->servicios = Servicio::where('tenant_id', $tenantId)
            ->where('activo', true)
            ->orderBy('nombre')
            ->get()
            ->toArray();
    }

    public function selectServicio(int $servicioId): void
    {
        $params = [
            'servicio' => $servicioId,
        ];

        if ($this->documento) {
            $params['documento'] = $this->documento;
        }

        if ($this->phone) {
            $params['phone'] = $this->phone;
        }

        $appointmentUrl = URL::temporarySignedRoute(
            'public.appointment',
            now()->addDays(7),
            array_merge(['tenant' => $this->tenantSlug], $params)
        );

        $this->redirect($appointmentUrl);
    }

    public function render()
    {
        return view('livewire.services')
            ->layout('layouts.public');
    }
}