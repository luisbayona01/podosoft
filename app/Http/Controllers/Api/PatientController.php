<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Paciente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PatientController extends Controller
{
    /**
     * Registra la ficha del paciente en la base de datos.
     * Es un paso obligatorio antes de agendar una cita.
     */
    public function register(Request $request)
    {
        \Illuminate\Support\Facades\Log::info('[PatientController] Iniciando registro de paciente', [
            'request' => $request->all()
        ]);

        // 1. Validation
        $validator = Validator::make($request->all(), [
            'tipo_documento' => 'required|string|max:10',
            'documento' => 'required|string|unique:pacientes,documento',
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'telefono' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'fecha_nacimiento' => 'nullable|date',
            'sexo' => 'nullable|string',
            'direccion' => 'nullable|string',
            'consentimiento' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            \Illuminate\Support\Facades\Log::warning('[PatientController] Error de validación al registrar paciente', [
                'errors' => $validator->errors()->toArray(),
                'request' => $request->all()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Datos de registro inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        // 2. Check Consent
        if (!$request->consentimiento) {
            return response()->json([
                'status' => 'error',
                'message' => 'Es obligatorio aceptar el consentimiento informado para registrarse.'
            ], 400);
        }

        try {
            // 3. Create Patient
            $paciente = Paciente::create([
                'tenant_id' => $request->tenant_id ?? 1, // Usar el tenant_id enviado o fallback a 1
                'tipo_documento' => $request->tipo_documento,
                'documento' => $request->documento,
                'nombre' => $request->nombre,
                'apellido' => $request->apellido,
                'telefono' => $request->telefono,
                'email' => $request->email,
                'fecha_nacimiento' => $request->fecha_nacimiento,
                'sexo' => $request->sexo,
                'direccion' => $request->direccion,
                'consentimiento' => true,
                'fecha_consentimiento' => now(),
            ]);

            \Illuminate\Support\Facades\Log::info('[PatientController] Paciente creado exitosamente en DB', [
                'paciente_id' => $paciente->id,
                'documento' => $paciente->documento
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Paciente registrado exitosamente',
                'data' => [
                    'paciente_id' => $paciente->id,
                    'documento' => $paciente->documento
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error interno al registrar el paciente: ' . $e->getMessage()
            ], 500);
        }
    }
}
