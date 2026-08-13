<?php

namespace Database\Factories;

use App\Models\Paciente;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class PacienteFactory extends Factory
{
    protected $model = Paciente::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'tipo_documento' => null,
            'documento' => null,
            'nombre' => $this->faker->firstName(),
            'apellido' => $this->faker->lastName(),
            'telefono' => $this->faker->numerify('3##########'),
            'email' => $this->faker->safeEmail(),
            'fecha_nacimiento' => $this->faker->date(),
            'sexo' => $this->faker->randomElement(['Masculino', 'Femenino']),
            'direccion' => $this->faker->address(),
            'consentimiento' => false,
        ];
    }
}