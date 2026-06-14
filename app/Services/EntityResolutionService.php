<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class EntityResolutionService
{
    /**
     * Resolves a value (ID or Name) to a real ID from the database.
     * 
     * @param string $tableName  The table to search in.
     * @param mixed $value       The value to resolve.
     * @param string $nameColumn The column to search for names.
     * @param int $tenantId      The current tenant ID.
     * @return array {
     *     'status' => 'success'|'ambiguous'|'not_found',
     *     'id' => int|null,
     *     'options' => array  // List of matches if ambiguous
     * }
     */
    public function resolve(string $tableName, mixed $value, string $nameColumn = 'nombre', int $tenantId = null): array
    {
        if (empty($value)) {
            return ['status' => 'not_found', 'id' => null, 'options' => []];
        }

        // 1. Check if it's a valid ID
        if (is_numeric($value)) {
            $exists = DB::table($tableName)
                ->where('id', $value)
                ->where('tenant_id', $tenantId)
                ->exists();

            if ($exists) {
                return ['status' => 'success', 'id' => (int)$value, 'options' => []];
            }
        }

        // 2. Search by name (case-insensitive, partial match)
        $matches = DB::table($tableName)
            ->select('id', $nameColumn)
            ->where('tenant_id', $tenantId)
            ->where($nameColumn, 'LIKE', '%' . $value . '%')
            ->get();

        if ($matches->isEmpty()) {
            return ['status' => 'not_found', 'id' => null, 'options' => []];
        }

        if ($matches->count() === 1) {
            return [
                'status' => 'success', 
                'id' => $matches->first()->id, 
                'options' => []
            ];
        }

        // 3. Ambiguous matches
        return [
            'status' => 'ambiguous',
            'id' => null,
            'options' => $matches->map(fn($m) => ['id' => $m->id, 'nombre' => $m->$nameColumn])->toArray()
        ];
    }
}
