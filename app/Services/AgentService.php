<?php

namespace App\Services;

use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\PatientController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AgentService
{
    public function __construct(
        protected AIService $aiService,
        protected ConversationService $conversationService,
        protected WhatsAppService $whatsAppService,
        protected EntityResolutionService $entityResolutionService
    ) {
    }

    public function handleMessage(string $phone, string $message, int $tenantId): array
    {
        $finalMessage = '';
        // 1. Cargar estado y contexto
        $rawState = $this->conversationService->getState($phone, $tenantId);
        $context = $this->buildContext($rawState, $tenantId);
        
        Log::channel('single')->info('[CONTEXT-AUDIT] 1. Context retrieved from DB', [
            'phone' => $phone,
            'tenant_id' => $tenantId,
            'context_initial' => $context
        ]);

        if (empty($context['telefono'])) {
            $context['telefono'] = $phone;
        }
        
        $history = $rawState['history'] ?? [];

        Log::info('[AgentService] Contexto aplanado enviado al modelo', $context);

        // 2. Primer llamado: el modelo analiza el mensaje del usuario
        $analysis = $this->aiService->analyzeMessage($message, $context, $history);
        $intent = $analysis['intent'] ?? 'UNKNOWN';
        $action = $analysis['action'] ?? 'UNKNOWN';
        $aiMessage = $analysis['message'] ?? '';
        $extractedData = $analysis['data'] ?? [];

        Log::channel('single')->info('[CONTEXT-AUDIT] 2. AI Analysis Result', [
            'message_received' => $message,
            'extracted_data' => $extractedData,
            'suggested_action' => $action,
            'suggested_intent' => $intent
        ]);

        Log::channel('single')->info('[AUDIT-FLOW] 1. IA Decision (Initial)', [
            'ai_suggested_intent' => $intent,
            'ai_suggested_action' => $action,
        ]);

        // 3. RESOLUCIÓN DE ENTIDADES Y MERGE
        $systemFeedback = null;
        $context = $this->mergeAndResolveExtracted($context, $extractedData, $tenantId, $systemFeedback);

        Log::channel('single')->info('[CONTEXT-AUDIT] 3. Context after Merge & Resolution', [
            'context_updated' => $context,
            'system_feedback' => $systemFeedback
        ]);

        Log::info('[AgentService] AI Analysis & Resolution Result', [
            'intent' => $intent,
            'action' => $action,
            'extracted_data' => $extractedData,
            'current_context' => $context,
            'resolution_feedback' => $systemFeedback
        ]);

        // 4. Ejecutar la acción del sistema (validar, registrar, buscar, etc.)
        Log::info('[AgentService] Ejecutando action', [
            'action'    => $action,
            'tenant_id' => $tenantId,
        ]);

        try {
            Log::channel('single')->info('[CONTEXT-AUDIT] 4. Executing Action', [
                'action' => $action,
                'context_at_execution' => $context
            ]);
            Log::channel('single')->info('[AUDIT-FLOW] 2. Laravel Execution', [
                'executing_action' => $action,
                'is_same_as_ai' => ($action === $analysis['action'] ?? 'UNKNOWN'),
                'override_reason' => ($action === ($analysis['action'] ?? 'UNKNOWN')) ? 'None' : 'Hardcoded override'
            ]);
            $systemResult = $this->executeAction($action, $context, $phone, $message, $tenantId);
        } catch (\Exception $e) {
            Log::error('[AgentService] ERROR CRÍTICO en executeAction', [
                'action' => $action,
                'error'  => $e->getMessage(),
                'trace'  => $e->getTraceAsString()
            ]);
            $systemResult = [
                'context' => $context,
                'message' => 'Lo siento, tuve un problema técnico al procesar tu solicitud. ¿Podrías intentar de nuevo en un momento?',
            ];
        }

        Log::info('[AgentService] Resultado de executeAction', [
            'action'        => $action,
            'es_null'       => $systemResult === null,
            'tiene_info'    => isset($systemResult['info']) && $systemResult['info'] !== '',
            'tiene_message' => isset($systemResult['message']),
        ]);

        if ($systemResult !== null) {
            $context    = $systemResult['context'];
            $systemInfo = $systemResult['info'] ?? '';

            Log::info('[AgentService] SystemResult recibido', [
                'tiene_info'  => !empty($systemInfo),
                'system_info' => $systemInfo,
            ]);

                if ($systemInfo) {
                    $secondAnalysis = $this->aiService->composeFromSystemResult(
                        $systemInfo,
                        $context,
                        $history
                    );

                    Log::info('[AgentService] Segunda respuesta de IA (composeFromSystemResult)', [
                        'second_intent'  => $secondAnalysis['intent']  ?? 'N/A',
                        'second_action'  => $secondAnalysis['action']  ?? 'N/A',
                        'second_message' => $secondAnalysis['message'] ?? 'N/A',
                        'second_data'    => $secondAnalysis['data']    ?? [],
                    ]);

                    $aiMessage = $secondAnalysis['message'] ?? $aiMessage;
                    $context   = $this->mergeExtracted($context, $secondAnalysis['data'] ?? []);

                     $secondAction = $secondAnalysis['action'] ?? 'UNKNOWN';
                     if ($secondAction !== 'UNKNOWN' && $secondAction !== $action && !str_starts_with($secondAction, 'GET_')) {
                         Log::channel('single')->info('[AUDIT-FLOW] 3. IA Decision (Secondary)', [
                             'ai_suggested_second_action' => $secondAction,
                         ]);
                         Log::info('[AgentService] Ejecutando acción secundaria detectada en redacción', [
                             'action' => $secondAction
                         ]);
                         $secondResult = $this->executeAction($secondAction, $context, $phone, $message, $tenantId);
                         
                         if ($secondResult !== null) {
                             $context = $secondResult['context'];
                             if (!empty($secondResult['info'])) {
                                 $aiMessage .= "\n\n" . $secondResult['info'];
                             } elseif (!empty($secondResult['message'])) {
                                 $aiMessage = $secondResult['message'];
                             }
                         }
                     } elseif ($secondAction !== 'UNKNOWN' && str_starts_with($secondAction, 'GET_')) {
                         // Ejecutar acciones de obtención de datos inmediatamente para incluirlas en la respuesta
                         Log::info('[AgentService] Ejecutando acción de datos secundaria', ['action' => $secondAction]);
                         $secondResult = $this->executeAction($secondAction, $context, $phone, $message, $tenantId);
                         if ($secondResult !== null && !empty($secondResult['info'])) {
                             $aiMessage .= "\n\n" . $secondResult['info'];
                             $context = $secondResult['context'];
                         }
                     }


                } else {
                $aiMessage = $systemResult['message'] ?? $aiMessage;

                Log::info('[AgentService] Mensaje directo del sistema (sin segundo llamado IA)', [
                    'message' => $aiMessage,
                ]);
            }
        }

        // 6. Persistir historial (máx 20 turnos = 40 partes)
        $history[] = ['role' => 'user',  'text' => $message];
        $history[] = ['role' => 'model', 'text' => $finalMessage];
        if (count($history) > 40) {
            $history = array_slice($history, -40);
        }

        // 7. Guardar estado aplanado + historial en DB
        Log::channel('single')->info('[CONTEXT-AUDIT] 5. State to be saved in DB', [
            'final_context' => $context,
            'intent' => $intent,
            'step' => $action
        ]);
        $this->conversationService->setState($phone, array_merge($context, [
            'history'   => $history,
            'intent'    => $intent,
            'step'      => $action,
            'tenant_id' => $tenantId,
        ]), $tenantId);

        // 8. Final Response Decision
        if (!empty($systemFeedback)) {
            Log::channel('single')->info('[AUDIT-FLOW] 4. Response Override', [
                'reason' => 'System feedback (Entity resolution failure)',
                'original_action' => $action,
                'final_action' => 'UNKNOWN'
            ]);
            $finalMessage = $systemFeedback;
            $action = 'UNKNOWN';
        } else {
            $finalMessage = $aiMessage ?: 'Lo siento, no estoy seguro de cómo ayudarte con eso.';
        }

        Log::info('[AgentService] Final Response Decision', [
            'final_message' => $finalMessage,
            'last_action' => $action,
            'context_snapshot' => $context
        ]);

        // 9. Enviar por WhatsApp
        $this->whatsAppService->sendText($phone, $finalMessage);

        return ['message' => $finalMessage];
    }

    private function mergeAndResolveExtracted(array $context, array $extracted, int $tenantId, ?string &$systemFeedback): array
    {
        $entitiesToResolve = [
            'servicio_id'    => 'servicios',
            'profesional_id' => 'profesionales',
            'sede_id'        => 'sedes',
        ];

        Log::info('[DEBUG-RESOLVE] Extracting data', ['extracted' => $extracted]);

        foreach ($extracted as $key => $value) {
            if ($value === null || $value === '') continue;

            if (array_key_exists($key, $entitiesToResolve)) {
                $tableName = $entitiesToResolve[$key];
                $resolution = $this->entityResolutionService->resolve($tableName, $value, 'nombre', $tenantId);

                Log::info('[DEBUG-RESOLVE] Resolution result', [
                    'key' => $key,
                    'value' => $value,
                    'status' => $resolution['status'],
                    'resolved_id' => $resolution['id'] ?? null
                ]);

                if ($resolution['status'] === 'success') {
                    $context[$key] = $resolution['id'];
                } elseif ($resolution['status'] === 'ambiguous') {
                    $options = array_map(fn($o) => $o['nombre'], $resolution['options']);
                    $entityName = str_replace('_id', '', $key);
                    $systemFeedback = "He encontrado varias opciones para {$entityName}: " . implode(', ', $options) . ". Por favor, indica cuál prefieres o escribe el número correspondiente.";
                } elseif ($resolution['status'] === 'not_found') {
                    $entityName = str_replace('_id', '', $key);
                    $systemFeedback = "No pude encontrar la opción '{$value}' para {$entityName}. Por favor, elige una de las opciones disponibles.";
                }
            } else {
                $context[$key] = $value;
            }
        }

        return $context;
    }

    private function mergeExtracted(array $context, array $extracted): array
    {
        foreach ($extracted as $key => $value) {
            if ($value !== null && $value !== '') {
                $context[$key] = $value;
            }
        }
        return $context;
    }

    protected function executeAction(string $action, array $context, string $phone, string $message, int $tenantId): ?array {
        switch ($action) {
            case 'VALIDATE_PATIENT':
                Log::info('[AgentService] VALIDATE_PATIENT - Iniciando validación', ['phone' => $phone, 'tenant_id' => $tenantId]);
                $documento = $context['documento'] ?? $this->extractDocumentFallback($message);
                if (!$documento) {
                    return ['context' => $context, 'message' => 'Para verificar tu registro necesito tu número de documento.'];
                }
                $context['documento'] = $documento;
                $exists = DB::table('pacientes')->where('documento', $documento)->where('tenant_id', $tenantId)->exists();
                $context['registered'] = $exists;
                return [
                    'context' => $context,
                    'info' => $exists ? "El paciente con documento {$documento} SÍ está registrado." : "El paciente con documento {$documento} NO se encuentra registrado. Solicita sus datos personales.",
                ];
            case 'CONFIRM_PATIENT_DATA':
                return null;
            case 'REGISTER_PATIENT':
                $consentimientoRaw = $context['consentimiento'] ?? false;
                $context['consentimiento'] = $this->parseBoolConsent($consentimientoRaw);
                if (!$this->hasRequiredPatientData($context)) {
                    $faltantes = array_filter(['tipo de documento' => empty($context['tipo_documento']) ? 'tipo de documento' : null, 'documento' => empty($context['documento']) ? 'documento' : null, 'nombre' => empty($context['nombre']) ? 'nombre' : null, 'apellido' => empty($context['apellido']) ? 'apellido' : null, 'consentimiento' => !$context['consentimiento'] ? 'consentimiento' : null]);
                    return ['context' => $context, 'info' => 'Faltan datos: ' . implode(', ', $faltantes)];
                }
                $response = $this->callController(PatientController::class, 'register', array_merge($context, ['tenant_id' => $tenantId]));
                if (($response['status'] ?? '') === 'success') {
                    $context['registered'] = true;
                    return ['context' => $context, 'info' => 'Paciente registrado exitosamente.'];
                }
                return ['context' => $context, 'message' => $response['message'] ?? 'Error al registrar.'];
            case 'GET_SERVICES':
            case 'ASK_SERVICE':
                $response = $this->callController(AppointmentController::class, 'getServices', ['tenant_id' => $tenantId]);
                $dataArray = json_decode(json_encode($response['data'] ?? []), true);
                if (empty($dataArray)) return ['context' => $context, 'message' => 'No hay servicios disponibles.'];
                $lista = $this->formatList($dataArray, 'nombre');
                return ['context' => $context, 'info' => "Servicios disponibles: {$lista}."];
            case 'GET_PROFESSIONALS':
            case 'ASK_PROFESSIONAL':
                $response = $this->callController(AppointmentController::class, 'getProfessionals', ['tenant_id' => $tenantId, 'servicio_id' => $context['servicio_id'] ?? null]);
                $dataArray = json_decode(json_encode($response['data'] ?? []), true);
                $lista = $this->formatList($dataArray, 'nombre');
                return ['context' => $context, 'info' => "Profesionales disponibles: {$lista}."];
            case 'GET_SEDES':
                $response = $this->callController(AppointmentController::class, 'getSedes', ['tenant_id' => $tenantId]);
                $dataArray = json_decode(json_encode($response['data'] ?? []), true);
                
                if (empty($dataArray)) {
                    Log::error('[AgentService] No sedes configuradas para el tenant', ['tenant_id' => $tenantId]);
                    return ['context' => $context, 'message' => 'Lo siento, no hay sedes configuradas en el sistema. Por favor, contacte al administrador.'];
                }

                if (count($dataArray) === 1) {
                    $sede = $dataArray[0];
                    $context['sede_id'] = $sede['id'];
                    Log::info('[AgentService] Sede asignada automáticamente', [
                        'sede_id' => $sede['id'],
                        'nombre' => $sede['nombre'],
                        'tenant_id' => $tenantId
                    ]);
                    return ['context' => $context, 'info' => "Se ha asignado automáticamente la sede: {$sede['nombre']}."];
                }

                $lista = $this->formatList($dataArray, 'nombre');
                return ['context' => $context, 'info' => "Sedes disponibles: {$lista}."];
            case 'GET_AVAILABILITY':
                $fecha = $context['fecha'] ?? null;
                $profesionalId = $context['profesional_id'] ?? null;
                if (!$fecha || !$profesionalId) return ['context' => $context, 'message' => 'Necesito la fecha y el profesional.'];
                $response = $this->callController(AppointmentController::class, 'getAvailability', ['fecha' => $fecha, 'profesional_id' => $profesionalId, 'tenant_id' => $tenantId]);
                $slots = $response['data']['available_slots'] ?? [];
                $info = empty($slots) ? "No hay horarios para el {$fecha}." : "Horarios para el {$fecha}: " . implode(', ', $slots);
                return ['context' => $context, 'info' => $info];
            case 'CHECK_APPOINTMENTS':
                $documento = $context['documento'] ?? null;
                if (!$documento) return ['context' => $context, 'message' => 'Para consultar tus citas necesito tu número de documento.'];
                $response = $this->callController(AppointmentController::class, 'getPatientAppointments', ['documento' => $documento, 'tenant_id' => $tenantId]);
                $citas = $response['data'] ?? [];
                if (empty($citas)) return ['context' => $context, 'info' => 'No tienes citas próximas programadas.'];
                $lista = "";
                foreach($citas as $c) {
                    $lista .= "[{$c['id']}] {$c['fecha_hora']} con {$c['profesional']} en {$c['sede']}\n";
                }
                return ['context' => $context, 'info' => "Tus citas próximas:\n{$lista}"];
            case 'MODIFY_APPOINTMENT':
                $citaId = $context['cita_id'] ?? null;
                if (empty($context['fecha_hora']) && !empty($context['fecha']) && !empty($context['hora'])) {
                    $context['fecha_hora'] = trim($context['fecha']) . ' ' . trim($context['hora']);
                }
                $fechaHora = $context['fecha_hora'] ?? null;
                if (!$citaId || !$fechaHora) return ['context' => $context, 'message' => 'Necesito el ID de la cita y la nueva fecha y hora (YYYY-MM-DD HH:mm).'];
                $response = $this->callController(AppointmentController::class, 'update', ['cita_id' => $citaId, 'fecha_hora' => $fechaHora, 'tenant_id' => $tenantId]);
                if (($response['status'] ?? '') === 'success') {
                    unset($context['cita_id'], $context['fecha_hora']);
                    return ['context' => $context, 'info' => 'Cita modificada exitosamente.'];
                }
                return ['context' => $context, 'message' => $response['message'] ?? 'Error al modificar la cita.'];
            case 'CREATE_APPOINTMENT':
                if (!$this->hasRequiredAppointmentData($context)) {
                    return ['context' => $context, 'info' => 'Faltan datos para crear la cita.'];
                }
                if (empty($context['fecha_hora']) && !empty($context['fecha']) && !empty($context['hora'])) {
                    $context['fecha_hora'] = trim($context['fecha']) . ' ' . trim($context['hora']);
                }
                $response = $this->callController(AppointmentController::class, 'store', array_merge($context, ['tenant_id' => $tenantId]));
                if (($response['status'] ?? '') === 'success') {
                    foreach (['servicio_id', 'profesional_id', 'sede_id', 'fecha', 'hora', 'fecha_hora'] as $k) unset($context[$k]);
                    return ['context' => $context, 'info' => 'Cita creada exitosamente.'];
                }
                return ['context' => $context, 'message' => $response['message'] ?? 'Error al agendar.'];
            default:
                return null;
        }
    }

    private function buildContext(array $rawState, int $tenantId): array
    {
        $data = $rawState['data'] ?? [];
        
        $tenant = DB::table('tenants')->where('id', $tenantId)->first();
        $clinicName = $tenant ? $tenant->nombre : 'nuestra clínica';

        return [
            'registered' => $rawState['registered'] ?? false,
            'documento' => $data['documento'] ?? $rawState['documento'] ?? null,
            'nombre' => $data['nombre'] ?? $rawState['nombre'] ?? null,
            'apellido' => $data['apellido'] ?? $rawState['apellido'] ?? null,
            'telefono' => $data['telefono'] ?? $rawState['telefono'] ?? null,
            'tipo_documento' => $data['tipo_documento'] ?? $rawState['tipo_documento'] ?? null,
            'servicio_id' => $data['servicio_id'] ?? $rawState['servicio_id'] ?? null,
            'profesional_id' => $data['profesional_id'] ?? $rawState['profesional_id'] ?? null,
            'sede_id' => $data['sede_id'] ?? $rawState['sede_id'] ?? null,
            'fecha' => $data['fecha'] ?? $rawState['fecha'] ?? null,
            'hora' => $data['hora'] ?? $rawState['hora'] ?? null,
            'fecha_hora' => $data['fecha_hora'] ?? $rawState['fecha_hora'] ?? null,
            'consentimiento' => $data['consentimiento'] ?? $rawState['consentimiento'] ?? false,
            'tenant_id' => $tenantId,
            'clinic_name' => $clinicName,
        ];
    }

    private function extractDocumentFallback(string $message): ?string
    {
        if (preg_match('/\b(\d{5,12})\b/', $message, $matches)) return $matches[1];
        return null;
    }

    private function formatList(array $items, string $labelField): string
    {
        if (empty($items)) return 'ninguno disponible';
        return implode("\n", array_map(fn($item) => "{$item['id']}. {$item[$labelField]}", $items));
    }

    private function hasRequiredPatientData(array $data): bool
    {
        foreach (['tipo_documento', 'documento', 'nombre', 'apellido', 'telefono'] as $field) {
            if (empty($data[$field])) return false;
        }
        return $data['consentimiento'] === true;
    }

    private function parseBoolConsent(mixed $value): bool
    {
        if (is_bool($value)) return $value;
        if (is_int($value)) return $value === 1;
        $str = mb_strtolower(trim((string) $value));
        if (empty($str)) return false;
        $positivePatterns = ['si', 'sí', 'yes', '1', 'true', 'acepto', 'aceptar', 'ok'];
        if (in_array($str, $positivePatterns, true)) return true;
        if (preg_match('/\b(si|sí|acepto|aceptar)\b/u', $str)) return true;
        return false;
    }

    private function hasRequiredAppointmentData(array $data): bool
    {
        $hasFechaHora = !empty($data['fecha_hora']) || (!empty($data['fecha']) && !empty($data['hora']));
        foreach (['documento', 'servicio_id', 'profesional_id', 'sede_id'] as $field) {
            if (empty($data[$field])) return false;
        }
        return $hasFechaHora;
    }

    protected function callController(string $controllerClass, string $method, array $params = []): array
    {
        $controller = app($controllerClass);
        $request = new Request();
        $request->merge($params);
        $response = $controller->$method($request);
        
        if (!$response || !method_exists($response, 'getContent')) {
            Log::error("[AgentService] Controller {$controllerClass}::{$method} returned an invalid response (null or not a Response object).");
            return [];
        }
        
        return json_decode($response->getContent(), true) ?? [];
    }
}
