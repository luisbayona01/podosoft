<?php

namespace Database\Seeders;

use App\Models\AntecedentType;
use Illuminate\Database\Seeder;

class AntecedentTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'Diabetes', 'is_risk' => true],
            ['name' => 'Hipertensión', 'is_risk' => true],
            ['name' => 'Alergias', 'is_risk' => true],
            ['name' => 'Anticoagulantes', 'is_risk' => true],
            ['name' => 'Pie Diabético', 'is_risk' => true],
            ['name' => 'Asma', 'is_risk' => false],
            ['name' => 'Gastritis', 'is_risk' => false],
            ['name' => 'Otros', 'is_risk' => false],
        ];

        foreach ($types as $type) {
            AntecedentType::updateOrCreate(['name' => $type['name']], $type);
        }
    }
}
