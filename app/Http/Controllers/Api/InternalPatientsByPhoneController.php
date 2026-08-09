<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Paciente;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Internal endpoint used by the Python AI service to check whether a phone
 * number is already associated with a registered patient in the given tenant.
 *
 * Auth: X-Service-Token header (same as the rest of /api/v1/*).
 *
 * Request:
 *   GET /api/v1/internal/patients/by-phone?phone=573001234567&tenant_id=1
 *
 * Response:
 *   200 {"registered": true,  "paciente_id": 5, "nombre": "Carlos", ...}
 *   200 {"registered": false, "paciente_id": null}
 */
class InternalPatientsByPhoneController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'phone'     => 'required|string|min:5|max:32',
                'tenant_id' => 'required|integer|min:1',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'code' => 'validation_failed',
                'message' => 'Invalid request',
                'errors' => $e->errors(),
            ], 422);
        }

        // Normalize the incoming phone to digits only (strips "+", spaces, dashes)
        // and compare against the stored telefono normalized the same way, so a
        // patient stored as "+573001234567" matches a webhook phone "573001234567".
        $normalized = preg_replace('/\D+/', '', $data['phone']) ?? '';

        $patient = Paciente::where('tenant_id', $data['tenant_id'])
            ->whereRaw("REGEXP_REPLACE(telefono, '[^0-9]', '') = ?", [$normalized])
            ->first();

        if (!$patient) {
            return response()->json(['registered' => false, 'paciente_id' => null]);
        }

        return response()->json([
            'registered'  => true,
            'paciente_id' => $patient->id,
            'documento'   => $patient->documento,
            'nombre'      => $patient->nombre,
            'apellido'    => $patient->apellido,
        ]);
    }
}
