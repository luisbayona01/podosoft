<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PacienteImportPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permission = Permission::firstOrCreate(['name' => 'importar pacientes']);

        $role = Role::firstOrCreate(['name' => 'Administrador']);

        if (!$role->hasPermissionTo($permission)) {
            $role->givePermissionTo($permission);
        }
    }
}