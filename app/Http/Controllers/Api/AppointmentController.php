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
            'servicio_id' => 'nullable|exists:servicios,id',
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
        $date = Carbon::createFromFormat('Y-m-d', $fecha)->startOfDay();

        // Duración del servicio si se conoce (default 30 min por slot)
        $stepMinutes = 30;
        if ($request->servicio_id) {
            $servicio = Servicio::find($request->servicio_id);
            if ($servicio && $servicio->duracion) {
                $stepMinutes = max(15, (int) $servicio->duracion);
            }
        }

        // Horario laboral del profesional para ese día de la semana
        // (horarios_profesionales.dia_semana guarda el nombre en español)
        $diasSemana = [
            1 => 'lunes', 2 => 'martes', 3 => 'miercoles', 4 => 'jueves',
            5 => 'viernes', 6 => 'sabado', 7 => 'domingo',
        ];
        $diaSemana = $diasSemana[$date->dayOfWeekIso];
        $horario = \App\Models\HorarioProfesional::where('profesional_id', $profesionalId)
            ->where('dia_semana', $diaSemana)
            ->where('activo', 1)
            ->first();

        // Fallback: jornada por defecto 08:00-18:00 si no hay horario configurado
        $startHour = 8;
        $endHour = 18;
        if ($horario) {
            $startHour = (int) substr($horario->hora_inicio, 0, 2);
            $endHour = (int) substr($horario->hora_fin, 0, 2);
        }

        // Bloqueos de agenda (vacaciones, licencias, etc.)
        $bloqueos = \App\Models\BloqueoAgenda::where('profesional_id', $profesionalId)
            ->where('fecha_inicio', '<=', $date->copy()->endOfDay())
            ->where('fecha_fin', '>=', $date)
            ->get();

        $slots = [];
        $start = $date->copy()->setTime($startHour, 0);
        $end = $date->copy()->setTime($endHour, 0);

        // No ofrecer horas pasadas si la fecha es hoy
        $now = now();

        while ($start->copy()->addMinutes($stepMinutes) <= $end) {
            $slotEnd = $start->copy()->addMinutes($stepMinutes);
            if ($start < $now) {
                $start->addMinutes($stepMinutes);
                continue;
            }
            $blocked = $bloqueos->contains(fn($b) =>
                Carbon::parse($b->fecha_inicio) < $slotEnd && Carbon::parse($b->fecha_fin) > $start
            );
            if (!$blocked) {
                $slots[] = $start->format('Y-m-d H:i');
            }
            $start->addMinutes($stepMinutes);
        }

        // Citas ocupadas (excluye canceladas) que se solapen con el slot
        $occupied = Cita::where('profesional_id', $profesionalId)
            ->whereDate('fecha_hora', $fecha)
            ->where('estado', '!=', 'cancelada')
            ->pluck('fecha_hora')
            ->map(fn($dt) => Carbon::parse($dt))
            ->toArray();

        $availableSlots = array_values(array_filter($slots, function ($slot) use ($occupied, $stepMinutes) {
            $slotStart = Carbon::createFromFormat('Y-m-d H:i', $slot);
            $slotEnd = $slotStart->copy()->addMinutes($stepMinutes);
            foreach ($occupied as $occ) {
                if ($occ < $slotEnd && $occ->copy()->addMinutes($stepMinutes) > $slotStart) {
                    return false;
                }
            }
            return true;
        }));

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
            ->where('estado', '!=', 'cancelada')
            ->orderBy('fecha_hora', 'asc')
            ->limit(5)
            ->get();

        $data = $citas->map(function ($cita) {
            $profesional = \App\Models\Profesional::find($cita->profesional_id);
            $sede = \App\Models\Sede::find($cita->sede_id);
            $servicio = $cita->servicios->first();
            return [
                'id' => $cita->id,
                'fecha_hora' => $cita->fecha_hora->format('Y-m-d H:i'),
                'servicio' => $servicio ? $servicio->nombre : null,
                'profesional' => $profesional?->nombre,
                'sede' => $sede?->nombre,
                'estado' => $cita->estado,
            ];
        });

        return response()->json(['status' => 'success', 'data' => $data]);
    }

    /**
     * Modifica una cita existente.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make(array_merge($request->all(), ['cita_id' => $id]), [
            'cita_id' => 'required|exists:citas,id',
            'fecha_hora' => 'required|date_format:Y-m-d H:i',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => 'Datos inválidos', 'errors' => $validator->errors()], 422);
        }

        $cita = Cita::findOrFail($id);

        if ($request->tenant_id && $cita->tenant_id != $request->tenant_id) {
            return response()->json(['status' => 'error', 'message' => 'Cita no encontrada'], 404);
        }

        if ($cita->estado === 'cancelada') {
            return response()->json(['status' => 'error', 'message' => 'La cita ya fue cancelada'], 422);
        }

        // Verificar disponibilidad del nuevo horario (excluyendo canceladas y la propia cita)
        $exists = Cita::where('profesional_id', $cita->profesional_id)
            ->where('fecha_hora', $request->fecha_hora)
            ->where('estado', '!=', 'cancelada')
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
     * Cancela una cita existente.
     */
    public function cancel(Request $request, $id)
    {
        $tenantId = $request->input('tenant_id');
        $motivo = $request->input('motivo');

        $cita = Cita::find($id);

        if (!$cita || ($tenantId && $cita->tenant_id != $tenantId)) {
            return response()->json(['status' => 'error', 'message' => 'Cita no encontrada'], 404);
        }

        if ($cita->estado === 'cancelada') {
            return response()->json(['status' => 'success', 'message' => 'La cita ya estaba cancelada', 'data' => ['cita_id' => $cita->id]]);
        }

        $cita->update(['estado' => 'cancelada']);

        Log::info('[AppointmentController::cancel] Cita cancelada', [
            'cita_id' => $cita->id,
            'tenant_id' => $cita->tenant_id,
            'motivo' => $motivo,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Cita cancelada exitosamente',
            'data' => ['cita_id' => $cita->id]
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
            'tenant_id' => 'required|exists:tenants,id',
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

        // 2. Find Patient (scoped to tenant)
        $paciente = Paciente::where('documento', $request->documento)
            ->where('tenant_id', $request->tenant_id)
            ->first();
        if (!$paciente) {
            return response()->json([
                'status' => 'error',
                'message' => 'Paciente no encontrado. Por favor, regístrelo primero.'
            ], 404);
        }

        // 3. Check Availability (excluye citas canceladas)
        $fecha = Carbon::parse($request->fecha_hora);
        $exists = Cita::where('profesional_id', $request->profesional_id)
            ->where('fecha_hora', $fecha)
            ->where('estado', '!=', 'cancelada')
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
                    'tenant_id' => $request->tenant_id,
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
