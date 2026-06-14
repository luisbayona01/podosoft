<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\Paciente;
use App\Models\Servicio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    /**
     * Consulta los horarios disponibles para un profesional en una fecha específica.
     */
    public function getAvailability(Request $request)
    {
        \Illuminate\Support\Facades\Log::info('[DEBUG-AVAILABILITY] Request received', [
            'payload' => $request->all()
        ]);

        $validator = Validator::make($request->all(), [
            'profesional_id' => 'required|exists:profesionales,id',
            'fecha' => 'required|date_format:Y-m-d',
        ]);

        if ($validator->fails()) {
            \Illuminate\Support\Facades\Log::warning('[DEBUG-AVAILABILITY] Validation failed', [
                'errors' => $validator->errors()->toArray(),
                'payload' => $request->all()
            ]);
            return response()->json(['status' => 'error', 'message' => 'Datos inválidos', 'errors' => $validator->errors()], 422);
        }

        $profesionalId = $request->profesional_id;
        $fecha = $request->fecha;

        $slots = [];
        $start = Carbon::createFromFormat('Y-m-d', $fecha)->setTime(8, 0);
        $end = Carbon::createFromFormat('Y-m-d', $fecha)->setTime(18, 0);

        while ($start < $end) {
            $slots[] = $start->format('Y-m-d H:i');
            $start->addMinutes(30);
        }

        $query = Cita::where('profesional_id', $profesionalId)
            ->whereDate('fecha_hora', $fecha);
        
        $occupiedSlots = $query->pluck('fecha_hora')
            ->map(fn($date) => Carbon::parse($date)->format('Y-m-d H:i'))
            ->toArray();

        $availableSlots = array_values(array_filter($slots, fn($slot) => !in_array($slot, $occupiedSlots)));

        \Illuminate\Support\Facades\Log::info('[DEBUG-AVAILABILITY] Result', [
            'available_count' => count($availableSlots),
            'occupied_count' => count($occupiedSlots)
        ]);

        return response()->json([
            'status' => 'success',
            'data' => [
                'fecha' => $fecha,
                'available_slots' => $availableSlots
            ]
        ]);
    }

    /**
     * Consulta las citas próximas de un paciente.
     */
    public function getPatientAppointments(Request $request)
    {
        $documento = $request->documento;
        $tenantId = $request->tenant_id;

        if (!$documento) {
            return response()->json(['status' => 'error', 'message' => 'Se requiere el documento del paciente'], 400);
        }

        $paciente = Paciente::where('documento', $documento)->where('tenant_id', $tenantId)->first();

        if (!$paciente) {
            return response()->json(['status' => 'error', 'message' => 'Paciente no encontrado'], 404);
        }

        $citas = Cita::where('paciente_id', $paciente->id)
            ->where('fecha_hora', '>=', now())
            ->orderBy('fecha_hora', 'asc')
            ->limit(3)
            ->get();

        $data = $citas->map(fn($cita) => [
            'id' => $cita->id,
            'fecha_hora' => $cita->fecha_hora->format('Y-m-d H:i'),
            'profesional' => \App\Models\Profesional::find($cita->profesional_id)->nombre,
            'sede' => \App\Models\Sede::find($cita->sede_id)->nombre,
        ]);

        return response()->json(['status' => 'success', 'data' => $data]);
    }

    /**
     * Modifica una cita existente.
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cita_id' => 'required|exists:citas,id',
            'fecha_hora' => 'required|date_format:Y-m-d H:i',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => 'Datos inválidos', 'errors' => $validator->errors()], 422);
        }

        $cita = Cita::findOrFail($request->cita_id);

        // Verificar disponibilidad del nuevo horario
        $exists = Cita::where('profesional_id', $cita->profesional_id)
            ->where('fecha_hora', $request->fecha_hora)
            ->where('id', '!=', $cita->id)
            ->exists();

        if ($exists) {
            return response()->json(['status' => 'conflict', 'message' => 'El nuevo horario solicitado ya está ocupado.'], 409);
        }

        $cita->update(['fecha_hora' => $request->fecha_hora]);

        return response()->json([
            'status' => 'success',
            'message' => 'Cita modificada exitosamente',
            'data' => [
                'cita_id' => $cita->id,
                'nueva_fecha_hora' => $cita->fecha_hora->format('d/m/Y H:i'),
            ]
        ]);
    }


    /**
     * Registra la cita final validando disponibilidad del profesional
     * en la fecha y hora solicitada.
     */
    public function store(Request $request)
    {
        // 1. Validation
        $validator = Validator::make($request->all(), [
            'documento' => 'required|string',
            'fecha_hora' => 'required|date_format:Y-m-d H:i',
            'servicio_id' => 'required|exists:servicios,id',
            'profesional_id' => 'required|exists:profesionales,id',
            'sede_id' => 'required|exists:sedes,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Datos inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        // 2. Find Patient
        $paciente = Paciente::where('documento', $request->documento)->first();
        if (!$paciente) {
            return response()->json([
                'status' => 'error',
                'message' => 'Paciente no encontrado. Por favor, regístrelo primero.'
            ], 404);
        }

        // 3. Check Availability
        $fecha = Carbon::parse($request->fecha_hora);
        $exists = Cita::where('profesional_id', $request->profesional_id)
            ->where('fecha_hora', $fecha)
            ->exists();

        if ($exists) {
            return response()->json([
                'status' => 'conflict',
                'message' => 'El horario solicitado ya está ocupado. Por favor, elija otra hora.'
            ], 409);
        }

        // 4. Create Appointment
        try {
                $cita = Cita::create([
                    'tenant_id' => 1, // Simplified, usually from API Key/Auth
                    'paciente_id' => $paciente->id,
                    'profesional_id' => $request->profesional_id,
                    'sede_id' => $request->sede_id,
                    'fecha_hora' => $fecha,
                    'estado' => 'Pendiente',
                    'origen' => 'ia'
                ]);

            // Attach service with price
            $servicio = Servicio::findOrFail($request->servicio_id);
            $cita->servicios()->attach($servicio->id, [
                'precio_aplicado' => $servicio->precio
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Cita agendada exitosamente',
                'data' => [
                    'cita_id' => $cita->id,
                    'fecha' => $cita->fecha_hora->format('d/m/Y H:i'),
                    'paciente' => $paciente->nombre . ' ' . $paciente->apellido,
                    'profesional' => \App\Models\Profesional::find($request->profesional_id)->nombre
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error interno al crear la cita: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Consulta la lista de servicios disponibles para que el agente los ofrezca al paciente.
     * Filtra por tenant_id y activo=1.
     */
    public function getServices(Request $request)
    {
        $tenantId = $request->tenant_id;

        Log::info('[AppointmentController::getServices] Consultando servicios', [
            'tenant_id' => $tenantId,
        ]);

        $query = Servicio::select(['id', 'nombre', 'duracion', 'precio'])
            ->where('activo', 1);

        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        $services = $query->get();

        Log::info('[AppointmentController::getServices] Resultado', [
            'tenant_id'       => $tenantId,
            'total_servicios' => $services->count(),
            'servicios'       => $services->toArray(),
        ]);

        if ($services->isEmpty()) {
            Log::warning('[AppointmentController::getServices] No se encontraron servicios activos', [
                'tenant_id' => $tenantId,
            ]);
        }

        return response()->json(['status' => 'success', 'data' => $services]);
    }

    /**
     * Consulta los profesionales activos y sus especialidades.
     */
    public function getProfessionals(Request $request)
    {
        $tenantId = $request->tenant_id;

        Log::info('[AppointmentController::getProfessionals] Consultando profesionales', [
            'tenant_id' => $tenantId,
        ]);

        $query = \App\Models\Profesional::select(['id', 'nombre', 'especialidad']);

        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        $professionals = $query->get();

        Log::info('[AppointmentController::getProfessionals] Resultado', [
            'total' => $professionals->count(),
            'data'  => $professionals->toArray(),
        ]);

        return response()->json(['status' => 'success', 'data' => $professionals]);
    }

    /**
     * Consulta las sedes físicas disponibles y sus direcciones.
     */
    public function getSedes(Request $request)
    {
        $tenantId = $request->tenant_id;

        Log::info('[AppointmentController::getSedes] Consultando sedes', [
            'tenant_id' => $tenantId,
        ]);

        $query = \App\Models\Sede::select(['id', 'nombre', 'direccion']);

        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        $sedes = $query->get();

        Log::info('[AppointmentController::getSedes] Resultado', [
            'total' => $sedes->count(),
            'data'  => $sedes->toArray(),
        ]);

        return response()->json(['status' => 'success', 'data' => $sedes]);
    }

}
