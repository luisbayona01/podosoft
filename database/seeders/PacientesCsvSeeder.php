<?php

namespace Database\Seeders;

use App\Models\Paciente;
use Illuminate\Database\Seeder;

class PacientesCsvSeeder extends Seeder
{
    /**
     * Importa pacientes desde database/seeders/data/pacientes_2016_2025.csv
     * Columnas: nombre, apellido, telefono, tipo_documento, documento,
     *           email, fecha_nacimiento, sexo, direccion
     * Los valores vacíos se insertan como null.
     */
    public function run(): void
    {
        $path = database_path('seeders/data/pacientes_2016_2025.csv');

        if (!file_exists($path)) {
            $this->command->error("No se encontró el archivo: {$path}");
            return;
        }

        $tenantId = (int) ($this->command->ask('Tenant ID', 1) ?: 1);

        $file = fopen($path, 'r');
        $header = fgetcsv($file);

        $created = 0;
        $skipped = 0;

        while (($row = fgetcsv($file)) !== false) {
            $data = array_combine($header, $row);

            // Valores vacíos => null
            $data = array_map(fn ($v) => ($v === '' || $v === null) ? null : trim($v), $data);

            // Obligatorios: nombre, apellido, teléfono
            if (!$data['nombre'] || !$data['apellido'] || !$data['telefono']) {
                $skipped++;
                continue;
            }

            // Clave única: si hay documento, evita duplicados; si no, inserta siempre
            $lookup = $data['documento']
                ? ['tenant_id' => $tenantId, 'documento' => $data['documento']]
                : ['id' => null];

            Paciente::updateOrCreate(
                $lookup,
                [
                    'nombre' => $data['nombre'],
                    'apellido' => $data['apellido'],
                    'telefono' => $data['telefono'],
                    'tipo_documento' => $data['tipo_documento'] ?? 'CC',
                    'email' => $data['email'],
                    'fecha_nacimiento' => $data['fecha_nacimiento'],
                    'sexo' => $data['sexo'],
                    'direccion' => $data['direccion'],
                ]
            );

            $created++;
        }

        fclose($file);

        $this->command->info("Pacientes importados/actualizados: {$created}. Omitidos (sin datos obligatorios): {$skipped}.");
    }
}
