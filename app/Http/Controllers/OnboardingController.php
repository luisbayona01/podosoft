<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\User;
use App\Models\Profesional;
use App\Models\Sede;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

class OnboardingController extends Controller
{
    public function submit(Request $request)
    {
        $data = $request->validate([
            'clinic_name' => 'required|string|max:255',
            'clinic_nit' => 'required|string|max:50',
            'clinic_email' => 'required|email|max:255',
            'clinic_city' => 'required|string|max:100',
            'clinic_address' => 'required|string|max:500',
            'admin_name' => 'required|string|max:255',
            'admin_apellido' => 'required|string|max:255',
            'admin_document' => 'required|string|min:5|max:50',
            'admin_licencia' => 'required|string|max:50',
            'admin_email' => 'required|email|max:255',
            'admin_password' => 'required|string|min:8|confirmed',
        ], [
            'clinic_name.required' => 'El nombre de la clínica es obligatorio.',
            'clinic_nit.required' => 'El NIT/RUT es obligatorio.',
            'clinic_email.required' => 'El correo electrónico es obligatorio.',
            'clinic_email.email' => 'Ingrese un correo electrónico válido.',
            'clinic_city.required' => 'La ciudad es obligatoria.',
            'clinic_address.required' => 'La dirección es obligatoria.',
            'admin_name.required' => 'El nombre es obligatorio.',
            'admin_apellido.required' => 'El apellido es obligatorio.',
            'admin_document.required' => 'El documento de identidad es obligatorio.',
            'admin_document.min' => 'El documento debe tener al menos 5 dígitos.',
            'admin_licencia.required' => 'El número de licencia es obligatorio.',
            'admin_email.required' => 'El correo electrónico es obligatorio.',
            'admin_email.email' => 'Ingrese un correo electrónico válido.',
            'admin_password.required' => 'La contraseña es obligatoria.',
            'admin_password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'admin_password.confirmed' => 'La confirmación de contraseña no coincide.',
        ]);

        try {
            $user = DB::transaction(function () use ($data) {
                $tenant = $this->createTenant($data);
                $professional = $this->createProfessional($tenant, $data);
                $user = $this->createUser($tenant, $professional, $data);
                $user->assignRole('Administrador');
                $this->createDefaultSede($tenant, $data);

                return $user;
            });

            Auth::login($user);
            return response()->json(['redirect' => url('/dashboard')]);
        } catch (QueryException $e) {
            return $this->handleDatabaseException($e);
        } catch (\Throwable $e) {
            \Log::error('Registration process failed: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'Ocurrió un error inesperado: ' . $e->getMessage()], 500);
        }
    }

    protected function createTenant(array $data): Tenant
    {
        $slug = $this->generateUniqueSlug(Str::slug($data['clinic_name']));

        return Tenant::create([
            'nombre' => $data['clinic_name'],
            'slug' => $slug,
            'nit' => $data['clinic_nit'],
            'email' => $data['clinic_email'],
            'direccion' => $data['clinic_address'],
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

    protected function createProfessional(Tenant $tenant, array $data): Profesional
    {
        return Profesional::create([
            'tenant_id' => $tenant->id,
            'nombre' => $data['admin_name'],
            'apellido' => $data['admin_apellido'],
            'documento' => $data['admin_document'],
            'numero_licencia' => $data['admin_licencia'],
            'email' => $data['admin_email'],
            'activo' => true,
        ]);
    }

    protected function createUser(Tenant $tenant, Profesional $professional, array $data): User
    {
        return User::create([
            'name' => $data['admin_name'],
            'email' => $data['admin_email'],
            'password' => Hash::make($data['admin_password']),
            'tenant_id' => $tenant->id,
            'documento' => $data['admin_document'],
            'professional_id' => $professional->id,
        ]);
    }

    protected function createDefaultSede(Tenant $tenant, array $data): Sede
    {
        return Sede::create([
            'tenant_id' => $tenant->id,
            'nombre' => 'Sede Principal',
            'direccion' => $data['clinic_address'],
            'activo' => true,
        ]);
    }

    protected function handleDatabaseException(QueryException $e)
    {
        $errorCode = $e->errorInfo[1 ?? 0] ?? 0;
        $message = $e->getMessage();

        if ($errorCode === 1062) {
            return $this->handleDuplicateEntryException($message);
        }

        if ($errorCode === 1452) {
            return response()->json(['error' => 'Error de referencia. Un registro requerido no existe.'], 422);
        }

        if ($errorCode === 1146 || $errorCode === '42S02' || str_contains(strtolower($message), 'table') && str_contains(strtolower($message), 'doesn\'t exist')) {
            return response()->json(['error' => 'Error de configuración del sistema. Tabla de base de datos no encontrada. Contacta al administrador.'], 500);
        }

        if ($errorCode === 1045 || $errorCode === 1049) {
            return response()->json(['error' => 'Error de conexión con la base de datos. Por favor intenta más tarde.'], 500);
        }

        \Log::error('Database error during registration', [
            'code' => $errorCode,
            'message' => $message,
            'trace' => $e->getTraceAsString(),
        ]);

        return response()->json(['error' => 'Error de base de datos. Por favor intenta nuevamente.'], 500);
    }

    protected function handleDuplicateEntryException(string $message)
    {
        if (str_contains($message, 'users_email_unique') || str_contains($message, 'users.email')) {
            return response()->json(['errors' => ['admin_email' => 'Este correo electrónico ya está registrado en el sistema.']], 422);
        }

        if (str_contains($message, 'profesionales_email_unique') || str_contains($message, 'profesionales.email')) {
            return response()->json(['errors' => ['admin_email' => 'Este correo ya está registrado como profesional.']], 422);
        }

        if (str_contains($message, 'profesionales_numero_licencia_unique') || str_contains($message, 'numero_licencia')) {
            return response()->json(['errors' => ['admin_licencia' => 'Esta licencia profesional ya está registrada.']], 422);
        }

        if (str_contains($message, 'tenants_email_unique') || str_contains($message, 'tenants.email')) {
            return response()->json(['errors' => ['clinic_email' => 'Este correo ya está registrado para otra clínica.']], 422);
        }

        if (str_contains($message, 'tenants_nit_unique') || str_contains($message, 'tenants.nit')) {
            return response()->json(['errors' => ['clinic_nit' => 'Este NIT/RUT ya está registrado para otra clínica.']], 422);
        }

        if (str_contains($message, 'tenants_slug_unique') || str_contains($message, 'tenants.slug')) {
            return response()->json(['errors' => ['clinic_name' => 'Ya existe una clínica con un nombre similar. Por favor usa otro nombre.']], 422);
        }

        if (str_contains($message, 'profesionales_documento_unique') || str_contains($message, 'profesionales.documento')) {
            return response()->json(['errors' => ['admin_document' => 'Este documento de identidad ya está registrado.']], 422);
        }

        if (str_contains($message, 'users_documento_unique') || str_contains($message, 'users.documento')) {
            return response()->json(['errors' => ['admin_document' => 'Este documento ya está asociado a otra cuenta.']], 422);
        }

        return response()->json(['error' => 'Ya existe un registro con algunos de los datos proporcionados. Por favor verifica la información.'], 422);
    }
}