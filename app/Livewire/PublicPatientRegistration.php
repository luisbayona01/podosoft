<?php

namespace App\Livewire;

use App\Events\PatientRegistered;
use App\Models\Paciente;
use App\Models\Tenant;
use App\Services\TenantResolverService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Livewire\Component;

class PublicPatientRegistration extends Component
{
    public string $tenantSlug;
    public array $tenantConfig = [];

    public string $tipo_documento = '';
    public string $documento = '';
    public string $nombre = '';
    public string $apellido = '';
    public string $telefono = '';
    public string $email = '';
    public string $fecha_nacimiento = '';
    public string $direccion = '';
    public bool $consentimiento = false;

    public array $tiposDocumento = [
        'CC' => 'Cédula de Ciudadanía',
        'TI' => 'Tarjeta de Identidad',
        'CE' => 'Cédula de Extranjería',
        'PA' => 'Pasaporte',
    ];

    public array $step = [
        'current' => 1,
        'total' => 3,
    ];

    public bool $isLoading = false;
    public ?string $errorMessage = null;
    public ?string $successMessage = null;

    protected $rules = [
        'tipo_documento' => 'required|in:CC,TI,CE,PA',
        'documento' => 'required|string|max:20',
        'nombre' => 'required|string|max:100',
        'apellido' => 'required|string|max:100',
        'telefono' => 'required|string|max:20',
        'email' => 'nullable|email|max:100',
        'fecha_nacimiento' => 'nullable|date|before:today',
        'direccion' => 'nullable|string|max:255',
        'consentimiento' => 'accepted',
    ];

    protected $messages = [
        'tipo_documento.required' => 'Seleccione el tipo de documento.',
        'documento.required' => 'Ingrese el número de documento.',
        'nombre.required' => 'Ingrese el nombre.',
        'apellido.required' => 'Ingrese el apellido.',
        'telefono.required' => 'Ingrese el número de WhatsApp.',
        'consentimiento.accepted' => 'Debe aceptar el tratamiento de datos.',
    ];

    public function mount(string $tenant): void
    {
        $this->tenantSlug = $tenant;
        $resolver = new TenantResolverService($tenant);
        $tenantModel = $resolver->resolve();

        if (!$tenantModel) {
            abort(404, 'Clínica no encontrada');
        }

        $this->tenantConfig = $resolver->getConfig();
        $this->telefono = request()->get('phone', '');
    }

    public function render()
    {
        return view('livewire.public-patient-registration')
            ->layout('layouts.public');
    }

    public function nextStep(): void
    {
        if ($this->step['current'] === 1) {
            $this->validate([
                'tipo_documento' => 'required|in:CC,TI,CE,PA',
                'documento' => 'required|string|max:20',
            ]);

            $tenant = Tenant::where('slug', $this->tenantSlug)->first();

            $existingPatient = Paciente::where('tenant_id', $tenant->id)
                ->where('tipo_documento', $this->tipo_documento)
                ->where('documento', $this->documento)
                ->first();

            if ($existingPatient) {
                $this->errorMessage = 'Este paciente ya se encuentra registrado.';
                return;
            }
        }

        if ($this->step['current'] === 2) {
            $this->validate([
                'nombre' => 'required|string|max:100',
                'apellido' => 'required|string|max:100',
                'telefono' => 'required|string|max:20',
            ]);
        }

        if ($this->step['current'] < $this->step['total']) {
            $this->step['current']++;
        }
    }

    public function previousStep(): void
    {
        if ($this->step['current'] > 1) {
            $this->step['current']--;
            $this->errorMessage = null;
        }
    }

    public function submit(): void
    {
        $this->isLoading = true;
        $this->errorMessage = null;

        try {
            $this->validate();

            $tenant = Tenant::where('slug', $this->tenantSlug)->first();

            $patient = Paciente::create([
                'tenant_id' => $tenant->id,
                'tipo_documento' => $this->tipo_documento,
                'documento' => $this->documento,
                'nombre' => strtoupper($this->nombre),
                'apellido' => strtoupper($this->apellido),
                'telefono' => $this->telefono,
                'email' => $this->email ?: null,
                'fecha_nacimiento' => $this->fecha_nacimiento ?: null,
                'direccion' => $this->direccion ?: null,
                'consentimiento' => true,
                'fecha_consentimiento' => now(),
            ]);

            event(new PatientRegistered($patient, $tenant, $this->telefono));

            $this->successMessage = "Registro exitoso. Bienvenido {$patient->nombre} {$patient->apellido}.";

            $servicesUrl = URL::temporarySignedRoute(
                'public.services',
                now()->addDays(7),
                [
                    'tenant' => $this->tenantSlug,
                    'documento' => $this->documento,
                    'phone' => $this->telefono,
                ]
            );

            $this->redirect($servicesUrl);

        } catch (\Exception $e) {
            $this->errorMessage = 'Error al registrar. Intente nuevamente.';
            report($e);
        } finally {
            $this->isLoading = false;
        }
    }

    public function updatedConsentimiento($value): void
    {
        if ($value) {
            $this->consentimiento = true;
        }
    }
}