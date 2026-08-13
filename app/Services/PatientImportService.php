<?php

namespace App\Services;

use App\Import\PatientCsvReader;
use App\Import\PatientImportResult;
use App\Import\PatientImportRow;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class PatientImportService
{
    public function __construct(
        protected PatientCsvReader $reader,
        protected PhoneNormalizerService $phoneNormalizer,
        protected PatientDuplicateFinder $duplicateFinder,
    ) {
    }

    public function analyze(string $content, int $tenantId): PatientImportResult
    {
        $parsed = $this->reader->read($content);

        $rows = [];

        foreach ($parsed['rows'] as $index => $rawRow) {
            $rows[] = [
                'rowNumber' => $index + 2,
                'raw' => $rawRow,
            ];
        }

        return $this->analyzeRows($rows, $tenantId);
    }

    public function analyzeRows(array $rows, int $tenantId): PatientImportResult
    {
        $prepared = [];

        foreach ($rows as $row) {
            $prepared[$row['rowNumber']] = $this->prepareRow($row);
        }

        $this->loadDuplicates($prepared, $tenantId);

        $seenDocuments = [];
        $seenPhones = [];

        foreach ($prepared as $rowNumber => &$preparedRow) {
            if ($preparedRow['status'] !== 'valid') {
                continue;
            }

            $documento = $preparedRow['data']['documento'];
            $telefono = $preparedRow['data']['telefono'];

            if ($documento !== null && $documento !== '') {
                if (isset($seenDocuments[$documento])) {
                    $preparedRow['status'] = 'duplicate';
                    $preparedRow['message'] = 'Duplicado - ya existe un paciente con este documento en el mismo archivo';
                    continue;
                }

                $seenDocuments[$documento] = true;
            }

            if ($telefono !== null && $telefono !== '') {
                if (isset($seenPhones[$telefono])) {
                    $preparedRow['status'] = 'duplicate';
                    $preparedRow['message'] = 'Duplicado - ya existe un paciente con este teléfono en el mismo archivo';
                    continue;
                }

                $seenPhones[$telefono] = true;
            }

            $result = $this->duplicateFinder->isDuplicate($preparedRow['data']);

            if ($result === 'documento') {
                $preparedRow['status'] = 'duplicate';
                $preparedRow['message'] = 'Duplicado - ya existe un paciente con este documento';
            } elseif ($result === 'telefono') {
                $preparedRow['status'] = 'duplicate';
                $preparedRow['message'] = 'Duplicado - ya existe un paciente con este teléfono';
            }
        }

        return new PatientImportResult(array_values($prepared));
    }

    public function import(array $preparedRows, int $tenantId): array
    {
        $batchSize = (int) config('patient-import.batch_size', 100);

        $imported = 0;
        $duplicates = 0;
        $errors = [];
        $newDetectedDuplicates = [];
        $validationFailures = [];
        $preDuplicateFailures = [];

        foreach ($preparedRows as $row) {
            $rowNumber = $row['rowNumber'];
            $status = $row['status'] ?? null;

            if ($status === 'error') {
                $validationFailures[$rowNumber] = $row['errors'] ?? [];
            } elseif ($status === 'duplicate') {
                $preDuplicateFailures[$rowNumber] = $row['message'] ?? 'Duplicado';
                $duplicates++;
            }
        }

        $validRows = array_values(array_filter($preparedRows, fn (array $row) => ($row['status'] ?? null) === 'valid'));

        DB::transaction(function () use ($validRows, $tenantId, $batchSize, &$imported, &$duplicates, &$errors, &$newDetectedDuplicates) {
            $this->duplicateFinder->forTenant($tenantId)->load(array_column($validRows, 'data'));

            $seenDocuments = [];
            $seenPhones = [];

            foreach (array_chunk($validRows, $batchSize) as $chunk) {
                foreach ($chunk as $row) {
                    $data = $row['data'];

                    $documento = $data['documento'];
                    $telefono = $data['telefono'];

                    $skipReason = null;

                    if ($documento !== null && $documento !== '') {
                        $seenDocuments[$documento] = true;

                        if ($this->duplicateFinder->findByDocument($documento) !== null) {
                            $skipReason = 'documento';
                        }
                    }

                    if ($skipReason === null && $telefono !== null && $telefono !== '') {
                        $seenPhones[$telefono] = true;

                        if ($this->duplicateFinder->findByPhone($telefono) !== null) {
                            $skipReason = 'telefono';
                        }
                    }

                    if ($skipReason !== null) {
                        $newDetectedDuplicates[$row['rowNumber']] = $skipReason === 'documento'
                            ? 'Duplicado - ya existe un paciente con este documento'
                            : 'Duplicado - ya existe un paciente con este teléfono';
                        $duplicates++;

                        continue;
                    }

                    try {
                        $attributes = $this->applyOptionalNulls($data, $tenantId);
                        \App\Models\Paciente::create($attributes);
                        $imported++;
                    } catch (Throwable $e) {
                        Log::warning('[PatientImport] error persistiendo fila ' . $row['rowNumber'], [
                            'error' => $e->getMessage(),
                        ]);
                        $errors[$row['rowNumber']] = 'Error al guardar el paciente: ' . $e->getMessage();
                    }
                }
            }
        });

        $failed = [];

        foreach ($validationFailures as $rowNumber => $messages) {
            $failed[] = [
                'rowNumber' => $rowNumber,
                'message' => implode(' | ', $messages),
            ];
        }

        foreach ($preDuplicateFailures as $rowNumber => $message) {
            $failed[] = [
                'rowNumber' => $rowNumber,
                'message' => $message,
            ];
        }

        foreach ($newDetectedDuplicates as $rowNumber => $message) {
            $failed[] = [
                'rowNumber' => $rowNumber,
                'message' => $message,
            ];
        }

        foreach ($errors as $rowNumber => $message) {
            $failed[] = [
                'rowNumber' => $rowNumber,
                'message' => $message,
            ];
        }

        usort($failed, fn (array $a, array $b) => $a['rowNumber'] <=> $b['rowNumber']);

        return [
            'found' => count($preparedRows),
            'imported' => $imported,
            'duplicates' => $duplicates,
            'errors_rows' => count($validationFailures) + count($errors),
            'omitted' => count($failed),
            'failed' => $failed,
        ];
    }

    protected function prepareRow(array $row): array
    {
        $dto = PatientImportRow::fromArray($row['raw'], $row['rowNumber']);

        $errors = [];

        $nombre = $dto->nombre;
        $apellido = $dto->apellido;
        $telefonoRaw = $dto->telefono;
        $tipoDocumento = $dto->tipoDocumento;
        $documento = $dto->documento;
        $email = $dto->email;
        $fechaNacimiento = $dto->fechaNacimiento;
        $sexo = $dto->sexo;
        $direccion = $dto->direccion;

        if ($nombre === null) {
            $errors[] = 'El nombre es obligatorio';
        } elseif (mb_strlen($nombre) > 255) {
            $errors[] = 'El nombre es demasiado largo';
        }

        if ($apellido === null) {
            $errors[] = 'El apellido es obligatorio';
        } elseif (mb_strlen($apellido) > 255) {
            $errors[] = 'El apellido es demasiado largo';
        }

        $telefono = null;

        if ($telefonoRaw === null) {
            $errors[] = 'El teléfono es obligatorio';
        } else {
            $telefono = $this->phoneNormalizer->normalize($telefonoRaw);

            if ($telefono === null || !$this->phoneNormalizer->isValid($telefono)) {
                $errors[] = 'El teléfono no es válido';
                $telefono = null;
            }
        }

        if ($tipoDocumento !== null && mb_strlen($tipoDocumento) > 10) {
            $errors[] = 'El tipo de documento no es válido';
        }

        if ($documento !== null && mb_strlen($documento) > 255) {
            $errors[] = 'El documento es demasiado largo';
        }

        if ($email !== null && filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $errors[] = 'El correo electrónico no es válido';
        }

        if ($fechaNacimiento !== null && !$this->isValidDate($fechaNacimiento)) {
            $errors[] = 'Fecha de nacimiento inválida';
        }

        $status = empty($errors) ? 'valid' : 'error';

        $data = $dto->toPacienteArray($telefono ?? '');

        $data['telefono'] = $telefono;
        $data['fecha_nacimiento'] = $fechaNacimiento === null ? null : $this->normalizeDate($fechaNacimiento);

        return [
            'rowNumber' => $row['rowNumber'],
            'status' => $status,
            'errors' => $errors,
            'message' => null,
            'raw' => $row['raw'],
            'data' => $data,
        ];
    }

    protected function loadDuplicates(array $prepared, int $tenantId): void
    {
        $this->duplicateFinder->forTenant($tenantId)->load(array_column($prepared, 'data'));
    }

    protected function applyOptionalNulls(array $data, int $tenantId): array
    {
        $data['tenant_id'] = $tenantId;

        foreach (['tipo_documento', 'documento', 'email', 'fecha_nacimiento', 'sexo', 'direccion'] as $field) {
            $data[$field] = $this->clean($data[$field] ?? null);
        }

        $data['consentimiento'] = false;

        return $data;
    }

    protected function clean(mixed $value): ?string
    {
        if ($value === null || !is_string($value) || trim($value) === '') {
            return null;
        }

        return trim($value);
    }

    protected function isValidDate(string $date): bool
    {
        return $this->parseDate($date) !== null;
    }

    protected function normalizeDate(string $date): string
    {
        return $this->parseDate($date)?->format('Y-m-d') ?? $date;
    }

    protected function parseDate(string $date): ?\DateTimeInterface
    {
        $formats = ['Y-m-d', 'd/m/Y', 'd-m-Y', 'Y/m/d'];

        foreach ($formats as $format) {
            $parsed = \DateTime::createFromFormat($format, $date);

            if ($parsed !== false) {
                $errors = \DateTime::getLastErrors();

                if ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0)) {
                    continue;
                }

                return $parsed;
            }
        }

        return null;
    }
}