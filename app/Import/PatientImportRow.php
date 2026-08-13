<?php

namespace App\Import;

class PatientImportRow
{
    public function __construct(
        public int $rowNumber,
        public ?string $nombre,
        public ?string $apellido,
        public ?string $telefono,
        public ?string $tipoDocumento,
        public ?string $documento,
        public ?string $email,
        public ?string $fechaNacimiento,
        public ?string $sexo,
        public ?string $direccion,
    ) {
    }

    public static function fromArray(array $data, int $rowNumber): self
    {
        return new self(
            rowNumber: $rowNumber,
            nombre: self::cleanValue($data['nombre'] ?? null),
            apellido: self::cleanValue($data['apellido'] ?? null),
            telefono: self::cleanValue($data['telefono'] ?? null),
            tipoDocumento: self::cleanValue($data['tipo_documento'] ?? null),
            documento: self::cleanValue($data['documento'] ?? null),
            email: self::cleanValue($data['email'] ?? null),
            fechaNacimiento: self::cleanValue($data['fecha_nacimiento'] ?? null),
            sexo: self::cleanValue($data['sexo'] ?? null),
            direccion: self::cleanValue($data['direccion'] ?? null),
        );
    }

    public function toPacienteArray(string $telefono): array
    {
        return [
            'tipo_documento' => $this->emptyToNull($this->tipoDocumento),
            'documento' => $this->emptyToNull($this->documento),
            'nombre' => $this->nombre,
            'apellido' => $this->apellido,
            'telefono' => $telefono,
            'email' => $this->emptyToNull($this->email),
            'fecha_nacimiento' => $this->emptyToNull($this->fechaNacimiento),
            'sexo' => $this->emptyToNull($this->sexo),
            'direccion' => $this->emptyToNull($this->direccion),
        ];
    }

    protected function emptyToNull(?string $value): ?string
    {
        return self::cleanValue($value);
    }

    protected static function cleanValue(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        return trim($value);
    }
}