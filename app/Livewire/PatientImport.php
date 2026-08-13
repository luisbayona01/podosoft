<?php

namespace App\Livewire;

use App\Services\PatientImportService;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

class PatientImport extends Component
{
    use WithFileUploads;

    #[Title('Importar pacientes')]
    public $file;

    public string $step = 'upload';

    public string $fileName = '';

    public int $fileSize = 0;

    public int $fileRows = 0;

    public int $maxFileSizeKb = 2048;

    public int $maxRows = 2000;

    public array $previewRows = [];

    public array $preparedRows = [];

    public array $counts = [
        'found' => 0,
        'valid' => 0,
        'errors' => 0,
        'duplicates' => 0,
    ];

    public array $summary = [];

    public string $uploadError = '';

    public string $processError = '';

    protected function rules(): array
    {
        return [
            'file' => ['required', 'file'],
        ];
    }

    public function mount(): void
    {
        $this->authorize('importar-pacientes');

        $this->maxFileSizeKb = (int) config('patient-import.max_file_size_kb');
        $this->maxRows = (int) config('patient-import.max_rows');
    }

    public function updatedFile(): void
    {
        $this->resetFileState();

        if ($this->file === null) {
            return;
        }

        $validation = $this->validateFile($this->file);

        if (!empty($validation)) {
            $this->uploadError = $validation;
            $this->file = null;

            return;
        }

        $this->fileName = $this->file->getClientOriginalName();
        $this->fileSize = $this->file->getSize();
    }

    public function parseFile(): void
    {
        $this->authorize('importar-pacientes');

        $this->processError = '';

        $this->validate();

        $validation = $this->validateFile($this->file);

        if (!empty($validation)) {
            $this->uploadError = $validation;

            return;
        }

        $tenantId = $this->tenantId();

        try {
            $service = app(PatientImportService::class);
            $result = $service->analyze($this->file->get(), $tenantId);

            if ($result->total() > $this->maxRows) {
                $this->processError = "El archivo supera el máximo de {$this->maxRows} filas permitidas.";

                return;
            }

            $this->preparedRows = $result->rows;
            $this->fileRows = $result->total();
            $this->counts = [
                'found' => $result->total(),
                'valid' => $result->validCount(),
                'errors' => $result->errorCount(),
                'duplicates' => $result->duplicateCount(),
            ];

            $previewLimit = (int) config('patient-import.preview_rows', 15);
            $this->previewRows = array_slice($this->preparedRows, 0, $previewLimit);

            $this->step = 'preview';
        } catch (\Throwable $e) {
            $this->processError = $e->getMessage();
        }
    }

    public function confirmImport(): void
    {
        $this->authorize('importar-pacientes');

        if (empty($this->preparedRows) || $this->step !== 'preview') {
            return;
        }

        $tenantId = $this->tenantId();

        $this->summary = app(PatientImportService::class)->import($this->preparedRows, $tenantId);

        $this->step = 'result';
    }

    public function downloadTemplate()
    {
        $this->authorize('importar-pacientes');

        $headers = config('patient-import.columns');
        $example = ['Carlos', 'Perez', '3001234567', '', '', '', '', '', ''];

        $content = implode(',', $headers) . "\n" . implode(',', $example) . "\n";

        return response()->streamDownload(function () use ($content) {
            echo $content;
        }, 'plantilla-importar-pacientes.csv', ['Content-Type' => 'text/csv']);
    }

    public function downloadErrors()
    {
        $this->authorize('importar-pacientes');

        $failedRows = $this->summary['failed'] ?? [];

        $headers = array_merge(config('patient-import.columns'), ['error']);

        $lines = [implode(',', $headers)];

        $rowsByNumber = [];

        foreach ($this->preparedRows as $row) {
            $rowsByNumber[$row['rowNumber']] = $row;
        }

        foreach ($failedRows as $failed) {
            $data = $rowsByNumber[$failed['rowNumber']]['data'] ?? [];
            $rowContent = [];

            foreach (config('patient-import.columns') as $column) {
                $rowContent[] = $this->csvCell($data[$column] ?? '');
            }

            $rowContent[] = $this->csvCell($failed['message']);

            $lines[] = implode(',', $rowContent);
        }

        $content = implode("\n", $lines) . "\n";

        return response()->streamDownload(function () use ($content) {
            echo $content;
        }, 'errores-importacion-pacientes.csv', ['Content-Type' => 'text/csv']);
    }

    public function startOver(): void
    {
        $this->resetImport();
    }

    protected function validateFile($file): string
    {
        $extension = strtolower(pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION));

        if (!in_array($extension, config('patient-import.allowed_extensions'), true)) {
            return 'La extensión del archivo no es válida. Solo se permiten archivos CSV (.csv) o TXT (.txt).';
        }

        $mime = strtolower($file->getMimeType() ?? '');

        if (!in_array($mime, config('patient-import.allowed_mimes'), true)) {
            return 'El tipo de archivo no es válido. Debe ser un archivo CSV.';
        }

        if ($file->getSize() > ($this->maxFileSizeKb * 1024)) {
            return "El archivo supera el tamaño máximo de {$this->maxFileSizeKb} KB.";
        }

        return '';
    }

    protected function tenantId(): int
    {
        return (int) (auth()->user()->tenant_id ?? 1);
    }

    protected function csvCell(mixed $value): string
    {
        $value = is_scalar($value) ? (string) $value : '';

        if (str_contains($value, ',') || str_contains($value, '"') || str_contains($value, "\n")) {
            return '"' . str_replace('"', '""', $value) . '"';
        }

        return $value;
    }

    protected function resetFileState(): void
    {
        $this->fileName = '';
        $this->fileSize = 0;
        $this->fileRows = 0;
        $this->uploadError = '';
        $this->processError = '';
        $this->previewRows = [];
        $this->preparedRows = [];
        $this->summary = [];
        $this->counts = [
            'found' => 0,
            'valid' => 0,
            'errors' => 0,
            'duplicates' => 0,
        ];
        $this->step = 'upload';
    }

    public function resetImport(): void
    {
        $this->resetFileState();
        $this->file = null;
    }

    public function render()
    {
        return view('livewire.patient-import');
    }
}