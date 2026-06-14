<?php

namespace Database\Seeders;

use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        Tenant::create([
            'nombre' => 'Clínica PodoSoft Central',
            'slug' => Str::slug('Clínica PodoSoft Central'),
            'email' => 'contacto@podosoft.com',
            'telefono' => '123456789',
            'direccion' => 'Calle Principal 123, Ciudad',
        ]);
    }
}
