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

class Onboarding extends Component
{
    public $step = 1;

    // Step 1: Clinic Info
    public $clinic_name;
    public $clinic_nit;
    public $clinic_phone;
    public $clinic_email;
    public $clinic_city;
    public $clinic_address;
    public $clinic_logo;

    // Step 2: Admin Info
    public $admin_name;
    public $admin_apellido;
    public $admin_document;
    public $admin_licencia;
    public $admin_email;
    public $admin_phone;
    public $admin_password;
    public $admin_password_confirmation;

    protected $rules = [
        'clinic_name' => 'required|string|max:255',
        'clinic_nit' => 'required|string|max:50',
        'clinic_phone' => 'required|regex:/^[0-9]+$/|max:20',
        'clinic_email' => 'required|email|max:255',
        'clinic_city' => 'required|string|max:100',
        'clinic_address' => 'required|string|max:500',

        'admin_name' => 'required|string|max:255',
        'admin_apellido' => 'required|string|max:255',
        'admin_document' => 'required|regex:/^[0-9]+$/|max:50',
        'admin_licencia' => 'required|string|max:50',
        'admin_email' => 'required|email|max:255|unique:users,email',
        'admin_phone' => 'required|regex:/^[0-9]+$/|max:20',
        'admin_password' => 'required|string|min:8|confirmed',
    ];

    public function nextStep()
    {
        if ($this->step == 1) {
            $this->validate([
                'clinic_name' => 'required|string|max:255',
                'clinic_nit' => 'required|string|max:50',
                'clinic_phone' => 'required|regex:/^[0-9]+$/|max:20',
                'clinic_email' => 'required|email|max:255',
                'clinic_city' => 'required|string|max:100',
                'clinic_address' => 'required|string|max:500',
            ]);
        } elseif ($this->step == 2) {
            $this->validate([
                'admin_name' => 'required|string|max:255',
                'admin_apellido' => 'required|string|max:255',
                'admin_document' => 'required|regex:/^[0-9]+$/|max:50',
                'admin_licencia' => 'required|string|max:50',
                'admin_email' => 'required|email|max:255|unique:users,email',
                'admin_phone' => 'required|regex:/^[0-9]+$/|max:20',
                'admin_password' => 'required|string|min:8|confirmed',
            ]);
        }

        $this->step++;
    }

    public function prevStep()
    {
        $this->step--;
    }

    public function register()
    {
        //$this->validate($this->rules);
        //dd($this->rules);
        try {
            DB::transaction(function () {
                $tenant = $this->executeStep('Tenant', function () {
                    return Tenant::create([
                        'nombre' => $this->clinic_name,
                        'slug' => Str::slug($this->clinic_name),
                        'nit' => $this->clinic_nit,
                        'telefono' => $this->clinic_phone,
                        'email' => $this->clinic_email,
                        'direccion' => $this->clinic_address,
                        'activo' => true,
                    ]);
                });

                $professional = $this->executeStep('Profesional', function () use ($tenant) {
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
                });

                $user = $this->executeStep('Usuario', function () use ($tenant, $professional) {
                    return User::create([
                        'name' => $this->admin_name,
                        'email' => $this->admin_email,
                        'password' => Hash::make($this->admin_password),
                        'tenant_id' => $tenant->id,
                        'documento' => $this->admin_document,
                        'telefono' => $this->admin_phone,
                        'professional_id' => $professional->id,
                    ]);
                });

                $this->executeStep('Asignación Rol', function () use ($user) {
                    $user->assignRole('Administrador');
                });

                $this->executeStep('Sede', function () use ($tenant) {
                    return Sede::create([
                        'tenant_id' => $tenant->id,
                        'nombre' => 'Sede Principal',
                        'direccion' => $this->clinic_address,
                        'activo' => true,
                    ]);
                });
            });
            Auth::login($user);
            return redirect()->to('/dashboard');
        } catch (\Throwable $e) {
            \Log::error('Registration process failed: ' . $e->getMessage());
            $this->addError('registration', 'Ocurrió un error durante el registro: ' . $e->getMessage());
            return null;
        }
    }

    private function executeStep(string $stepName, callable $callback)
    {
        \Log::info("Executing registration step: $stepName");
        try {
            $result = $callback();
            \Log::info("Successfully completed registration step: $stepName");
            return $result;
        } catch (\Throwable $e) {
            \Log::error("Error in registration step [$stepName]: " . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            throw new \Exception("Error en el paso [$stepName]: " . $e->getMessage(), 0, $e);
        }
    }

    public function render()
    {
        return view('livewire.auth.onboarding')
            ->layout('layouts.guest');
    }
}
