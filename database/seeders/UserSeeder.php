<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::first() ??
            Tenant::create([
                'nombre' => 'Default Tenant',
                'slug' => 'default-tenant',
                'email' => 'default@tenant.com',
                'telefono' => '000000000',
                'direccion' => 'Default Address',
            ]);

        $user = User::create([
            'name' => 'Administrador PodoSoft',
            'email' => 'admin@podosoft.com',
            'password' => Hash::make('password'),
            'tenant_id' => $tenant->id,
        ]);

        $user->assignRole('Administrador');
    }
}
