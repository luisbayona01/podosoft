<?php

namespace App\Import;

use RuntimeException;

class PatientCsvReader
{
    public function __construct(
        protected CsvDelimiterDetector $delimiterDetector,
    ) {
    }

    protected array $columnAliases = [
        'nombre' => ['nombre', 'nombre(s)', 'nombres', 'name', 'first_name', 'nombres y apellidos'],
        'apellido' => ['apellido', 'apellido(s)', 'apellidos', 'last_name', 'lastname', 'apellidos y nombres'],
        'telefono' => ['telefono', 'teléfono', 'tel', 'phone', 'celular', 'cel', 'movil', 'móvil'],
        'tipo_documento' => ['tipo_documento', 'tipo documento', 'tipodocumento', 'document_type', 'tipo de documento'],
        'documento' => ['documento', 'número documento', 'num documento', 'numero documento', 'numero', 'número', 'cc', 'document', 'identificacion', 'identificación'],
        'email' => ['email', 'correo', 'correo electronico', 'correo electrónico', 'mail', 'e-mail'],
        'fecha_nacimiento' => ['fecha_nacimiento', 'fecha nacimiento', 'fechanacimiento', 'nacimiento', 'birthday', 'birth_date', 'fecha de nacimiento'],
        'sexo' => ['sexo', 'genero', 'género'],
        'direccion' => ['direccion', 'dirección', 'address', 'domicilio'],
    ];

    public function read(string $content): array
    {
        $content = $this->sanitizeEncoding($content);

        $handle = fopen('php://temp', 'r+');
        fwrite($handle, $content);
        rewind($handle);

        $delimiter = $this->delimiterDetector->detect($content);

        $rawRows = [];
        while (($data = fgetcsv($handle, 0, $delimiter, '"', '\\')) !== false) {
            $rawRows[] = $data;
        }

        fclose($handle);

        if (empty($rawRows)) {
            throw new RuntimeException('El archivo CSV está vacío.');
        }

        $header = $this->normalizeHeaders(array_shift($rawRows));

        $missing = array_diff(config('patient-import.required_columns'), array_keys($header));

        if (!empty($missing)) {
            throw new RuntimeException(
                'La cabecera del CSV no contiene las columnas obligatorias: ' . implode(', ', $missing) . '.'
            );
        }

        $rows = [];

        foreach ($rawRows as $index => $raw) {
            if ($this->isEmptyRow($raw)) {
                continue;
            }

            $row = [];

            foreach ($header as $canonical => $indexInRaw) {
                $row[$canonical] = isset($raw[$indexInRaw]) ? trim($raw[$indexInRaw]) : '';
            }

            $rows[] = $row;
        }

        return [
            'headers' => array_keys($header),
            'rows' => $rows,
        ];
    }

    protected function sanitizeEncoding(string $content): string
    {
        return preg_replace('/^\xEF\xBB\xBF/', '', $content);
    }

    protected function normalizeHeaders(array $header): array
    {
        $mapping = [];

        foreach ($header as $position => $rawHeader) {
            $rawHeader = is_string($rawHeader) ? $rawHeader : '';
            $normalized = $this->normalizeHeaderName($rawHeader);

            if ($normalized === '') {
                continue;
            }

            $mapping[$normalized] = $position;
        }

        return $mapping;
    }

    protected function normalizeHeaderName(string $header): string
    {
        $normalized = mb_strtolower(trim($header));
        $normalized = str_replace(['á', 'é', 'í', 'ó', 'ú', 'ñ'], ['a', 'e', 'i', 'o', 'u', 'n'], $normalized);
        $normalized = preg_replace('/\s+/', ' ', $normalized);
        $normalized = trim($normalized, " \t\n\r\0\x0B\"'");

        foreach ($this->columnAliases as $canonical => $aliases) {
            if ($normalized === $canonical || in_array($normalized, $aliases, true)) {
                return $canonical;
            }
        }

        return $normalized;
    }

    protected function isEmptyRow(array $row): bool
    {
        foreach ($row as $cell) {
            if (is_string($cell) && trim($cell) !== '') {
                return false;
            }
        }

        return true;
    }
}