<?php

namespace App\Import;

class PatientImportResult
{
    public function __construct(public array $rows = [])
    {
    }

    public function validCount(): int
    {
        return count($this->rowsByStatus('valid'));
    }

    public function errorCount(): int
    {
        return count($this->rowsByStatus('error'));
    }

    public function duplicateCount(): int
    {
        return count($this->rowsByStatus('duplicate'));
    }

    public function total(): int
    {
        return count($this->rows);
    }

    public function rowsByStatus(string $status): array
    {
        return array_values(array_filter($this->rows, fn (array $row) => ($row['status'] ?? null) === $status));
    }

    public function validRows(): array
    {
        return $this->rowsByStatus('valid');
    }
}