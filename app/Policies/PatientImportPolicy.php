<?php

namespace App\Policies;

use App\Models\User;

class PatientImportPolicy
{
    public function import(User $user): bool
    {
        $permission = config('patient-import.permission', 'importar pacientes');

        if (!\Spatie\Permission\Models\Permission::where('name', $permission)->exists()) {
            return true;
        }

        return $user->hasDirectPermission($permission)
            || $user->hasPermissionTo($permission);
    }
}