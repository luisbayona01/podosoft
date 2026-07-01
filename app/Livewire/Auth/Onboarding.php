<?php

namespace App\Livewire\Auth;

use App\Models\Tenant;
use App\Models\User;
use App\Models\Profesional;
use App\Models\Sede;
use Livewire\Component;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

class Onboarding extends Component
{
    public $step = 1;

    public $clinic_name = '';
    public $clinic_nit = '';
    public $clinic_phone = '';
    public $clinic_email = '';
    public $clinic_city = '';
    public $clinic_address = '';

    public $admin_name = '';
    public $admin_apellido = '';
    public $admin_document = '';
    public $admin_licencia = '';
    public $admin_email = '';
    public $admin_phone = '';
    public $admin_password = '';
    public $admin_password_confirmation = '';

    protected $rules = [
        'clinic_name' => 'required|string|max:255',
        'clinic_nit' => 'required|string|max:50',
        'clinic_phone' => 'required|string|min:7|max:20',
        'clinic_email' => 'required|email|max:255',
        'clinic_city' => 'required|string|max:100',
        'clinic_address' => 'required|string|max:500',
        'admin_name' => 'required|string|max:255',
        'admin_apellido' => 'required|string|max:255',
        'admin_document' => 'required|string|min:5|max:50',
        'admin_licencia' => 'required|string|max:50',
        'admin_email' => 'required|email|max:255',
        'admin_phone' => 'required|string|min:7|max:20',
        'admin_password' => 'required|string|min:8|confirmed',
    ];

    protected function messages(): array
    {
        return [
            'clinic_name.required' => 'El nombre de la clínica es obligatorio.',
            'clinic_nit.required' => 'El NIT/RUT es obligatorio.',
            'clinic_phone.required' => 'El teléfono es obligatorio.',
            'clinic_phone.min' => 'El teléfono debe tener al menos 7 dígitos.',
            'clinic_email.required' => 'El correo electrónico es obligatorio.',
            'clinic_email.email' => 'Ingrese un correo electrónico válido.',
            'clinic_city.required' => 'La ciudad es obligatoria.',
            'clinic_address.required' => 'La dirección es obligatorio.',
            'admin_name.required' => 'El nombre es obligatorio.',
            'admin_apellido.required' => 'El apellido es obligatorio.',
            'admin_document.required' => 'El documento de identidad es obligatorio.',
            'admin_document.min' => 'El documento debe tener al menos 5 dígitos.',
            'admin_licencia.required' => 'El número de licencia es obligatorio.',
            'admin_email.required' => 'El correo electrónico es obligatorio.',
            'admin_email.email' => 'Ingrese un correo electrónico válido.',
            'admin_phone.required' => 'El teléfono es obligatorio.',
            'admin_phone.min' => 'El teléfono debe tener al menos 7 dígitos.',
            'admin_password.required' => 'La contraseña es obligatoria.',
            'admin_password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'admin_password.confirmed' => 'La confirmación de contraseña no coincide.',
        ];
    }

    public function mount()
    {
        $stored = json_decode(request()->cookie('onboarding_data', '{}'), true) ?: [];
        if (!is_array($stored)) {
            $stored = [];
        }

        $this->clinic_name = $stored['clinic_name'] ?? '';
        $this->clinic_nit = $stored['clinic_nit'] ?? '';
        $this->clinic_phone = $stored['clinic_phone'] ?? '';
        $this->clinic_email = $stored['clinic_email'] ?? '';
        $this->clinic_city = $stored['clinic_city'] ?? '';
        $this->clinic_address = $stored['clinic_address'] ?? '';
        $this->admin_name = $stored['admin_name'] ?? '';
        $this->admin_apellido = $stored['admin_apellido'] ?? '';
        $this->admin_document = $stored['admin_document'] ?? '';
        $this->admin_licencia = $stored['admin_licencia'] ?? '';
        $this->admin_email = $stored['admin_email'] ?? '';
        $this->admin_phone = $stored['admin_phone'] ?? '';
        $this->step = $stored['step'] ?? 1;
    }

    public function saveToStorage()
    {
        $data = [
            'step' => $this->step,
            'clinic_name' => $this->clinic_name,
            'clinic_nit' => $this->clinic_nit,
            'clinic_phone' => $this->clinic_phone,
            'clinic_email' => $this->clinic_email,
            'clinic_city' => $this->clinic_city,
            'clinic_address' => $this->clinic_address,
            'admin_name' => $this->admin_name,
            'admin_apellido' => $this->admin_apellido,
            'admin_document' => $this->admin_document,
            'admin_licencia' => $this->admin_licencia,
            'admin_email' => $this->admin_email,
            'admin_phone' => $this->admin_phone,
        ];

        return response()->json(['saved' => true])
            ->cookie('onboarding_data', json_encode($data), 60 * 24);
    }

    public function nextStep()
    {
        if ($this->step === 1) {
            $this->validate([
                'clinic_name' => 'required|string|max:255',
                'clinic_nit' => 'required|string|max:50',
                'clinic_phone' => 'required|string|min:7|max:20',
                'clinic_email' => 'required|email|max:255',
                'clinic_city' => 'required|string|max:100',
                'clinic_address' => 'required|string|max:500',
            ]);
        } elseif ($this->step === 2) {
            $this->validate([
                'admin_name' => 'required|string|max:255',
                'admin_apellido' => 'required|string|max:255',
                'admin_document' => 'required|string|min:5|max:50',
                'admin_licencia' => 'required|string|max:50',
                'admin_email' => 'required|email|max:255',
                'admin_phone' => 'required|string|min:7|max:20',
                'admin_password' => 'required|string|min:8|confirmed',
            ]);
        }

        if ($this->getErrorBag()->isEmpty()) {
            $this->step++;
        }
    }

    public function prevStep()
    {
        if ($this->step > 1) {
            $this->step--;
        }
    }

    public function goToStep(int $step)
    {
        if ($step >= 1 && $step < $this->step) {
            $this->step = $step;
        }
    }

    public function register()
    {
        if ($this->step !== 3) {
            $this->addError('registration', 'Por favor completa todos los pasos antes de finalizar.');
            return null;
        }

        $this->validate($this->rules);

        try {
            $user = DB::transaction(function () {
                $tenant = $this->createTenant();
                $professional = $this->createProfessional($tenant);
                $user = $this->createUser($tenant, $professional);
                $this->assignAdminRole($user);
                $this->createDefaultSede($tenant);

                return $user;
            });

            Auth::login($user);

            response()->json(['redirect' => '/dashboard'])
                ->cookie('onboarding_data', '', -1);

            return redirect()->to('/dashboard');
        } catch (QueryException $e) {
            $this->handleDatabaseException($e);
            return null;
        } catch (\Throwable $e) {
            \Log::error('Registration process failed: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->handleGenericException($e);
            return null;
        }
    }

    protected function handleGenericException(\Throwable $e): void
    {
        $message = $e->getMessage();

        if (str_contains(strtolower($message), 'roles') ||
            str_contains(strtolower($message), 'table') && str_contains(strtolower($message), 'doesn\'t exist') ||
            str_contains(strtolower($message), 'assignrole') ||
            str_contains(strtolower($message), 'spatie')) {
            $this->addError('registration', 'Error de configuración del sistema de permisos. Por favor contacta al administrador.');
            return;
        }

        $this->addError('registration', 'Ocurrió un error inesperado: ' . $message);
    }

    protected function createTenant(): Tenant
    {
        $slug = $this->generateUniqueSlug(Str::slug($this->clinic_name));

        return Tenant::create([
            'nombre' => $this->clinic_name,
            'slug' => $slug,
            'nit' => $this->clinic_nit,
            'telefono' => $this->clinic_phone,
            'email' => $this->clinic_email,
            'direccion' => $this->clinic_address,
            'activo' => true,
        ]);
    }

    protected function generateUniqueSlug(string $baseSlug): string
    {
        $slug = $baseSlug;
        $counter = 1;

        while (Tenant::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    protected function createProfessional(Tenant $tenant): Profesional
    {
        return Profesional::create([
            'tenant_id' => $tenant->id,
            'nombre' => $this->admin_name,
            'apellido' => $this->admin_apellido,
            'documento' => $this->admin_document,
            'numero_licencia' => $this->admin_licencia,
            'telefono' => $this->admin_phone,
            'email' => $this->admin_email,
            'activo' => true,
        ]);
    }

    protected function createUser(Tenant $tenant, Profesional $professional): User
    {
        return User::create([
            'name' => $this->admin_name,
            'email' => $this->admin_email,
            'password' => Hash::make($this->admin_password),
            'tenant_id' => $tenant->id,
            'documento' => $this->admin_document,
            'telefono' => $this->admin_phone,
            'professional_id' => $professional->id,
        ]);
    }

    protected function assignAdminRole(User $user): void
    {
        $user->assignRole('Administrador');
    }

    protected function createDefaultSede(Tenant $tenant): Sede
    {
        return Sede::create([
            'tenant_id' => $tenant->id,
            'nombre' => 'Sede Principal',
            'direccion' => $this->clinic_address,
            'activo' => true,
        ]);
    }

    protected function handleDatabaseException(QueryException $e): void
    {
        $errorCode = $e->errorInfo[1 ?? 0] ?? 0;
        $message = $e->getMessage();

        if ($errorCode === 1062) {
            $this->handleDuplicateEntryException($message);
            return;
        }

        if ($errorCode === 1452) {
            $this->addError('registration', 'Error de referencia. Un registro requerido no existe.');
            return;
        }

        if ($errorCode === 1146 || $errorCode === '42S02' || str_contains(strtolower($message), 'table') && str_contains(strtolower($message), 'doesn\'t exist')) {
            $this->addError('registration', 'Error de configuración del sistema. Tabla de base de datos no encontrada. Contacta al administrador.');
            return;
        }

        if ($errorCode === 1045 || $errorCode === 1049) {
            $this->addError('registration', 'Error de conexión con la base de datos. Por favor intenta más tarde.');
            return;
        }

        \Log::error('Database error during registration', [
            'code' => $errorCode,
            'message' => $message,
            'trace' => $e->getTraceAsString(),
        ]);

        $this->addError('registration', 'Error de base de datos. Por favor intenta nuevamente.');
    }

    protected function handleDuplicateEntryException(string $message): void
    {
        if (str_contains($message, 'users_email_unique') || str_contains($message, 'users.email')) {
            $this->addError('admin_email', 'Este correo electrónico ya está registrado en el sistema.');
            $this->step = 2;
            return;
        }

        if (str_contains($message, 'profesionales_email_unique') || str_contains($message, 'profesionales.email')) {
            $this->addError('admin_email', 'Este correo ya está registrado como profesional.');
            $this->step = 2;
            return;
        }

        if (str_contains($message, 'profesionales_numero_licencia_unique') || str_contains($message, 'numero_licencia')) {
            $this->addError('admin_licencia', 'Esta licencia profesional ya está registrada.');
            $this->step = 2;
            return;
        }

        if (str_contains($message, 'tenants_email_unique') || str_contains($message, 'tenants.email')) {
            $this->addError('clinic_email', 'Este correo ya está registrado para otra clínica.');
            $this->step = 1;
            return;
        }

        if (str_contains($message, 'tenants_nit_unique') || str_contains($message, 'tenants.nit')) {
            $this->addError('clinic_nit', 'Este NIT/RUT ya está registrado para otra clínica.');
            $this->step = 1;
            return;
        }

        if (str_contains($message, 'tenants_slug_unique') || str_contains($message, 'tenants.slug')) {
            $this->addError('clinic_name', 'Ya existe una clínica con un nombre similar. Por favor usa otro nombre.');
            $this->step = 1;
            return;
        }

        if (str_contains($message, 'profesionales_documento_unique') || str_contains($message, 'profesionales.documento')) {
            $this->addError('admin_document', 'Este documento de identidad ya está registrado.');
            $this->step = 2;
            return;
        }

        if (str_contains($message, 'users_documento_unique') || str_contains($message, 'users.documento')) {
            $this->addError('admin_document', 'Este documento ya está asociado a otra cuenta.');
            $this->step = 2;
            return;
        }

        $this->addError('registration', 'Ya existe un registro con algunos de los datos proporcionados. Por favor verifica la información.');
    }

    public function render()
    {
        return view('livewire.auth.onboarding')
            ->layout('layouts.guest');
    }
}