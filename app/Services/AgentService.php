<?php

namespace App\Services;

use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\PatientController;
use App\Models\Paciente;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class AgentService
{
    public function __construct(
        protected AIService $aiService,
        protected ConversationService $conversationService,
        protected WhatsAppService $whatsAppService,
        protected EntityResolutionService $entityResolutionService,
        protected PatientValidationService $patientValidationService
    ) {
    }

    public function handleMessage(string $phone, string $message, int $tenantId): array
    {
        $finalMessage = '';

        $tenant = Tenant::find($tenantId);
        $clinicName = $tenant ? $tenant->nombre : 'nuestra clínica';

        $rawState = $this->conversationService->getState($phone, $tenantId);
        $context = $this->buildContext($rawState, $tenantId, $clinicName);

        Log::channel('single')->info('[CONTEXT-AUDIT] 1. Context retrieved from DB', [
            'phone' => $phone,
            'tenant_id' => $tenantId,
            'context_initial' => $context
        ]);

        if (empty($context['telefono'])) {
            $context['telefono'] = $phone;
        }

        $history = $rawState['history'] ?? [];
        $context['clinic_name'] = $clinicName;
        $context['tenant_slug'] = $tenant?->slug ?? '';

        Log::info('[AgentService] Contexto aplanado enviado al modelo', $context);

        $conversationStep = $context['conversation_step'] ?? null;

        if ($conversationStep === 'WAITING_DOCUMENT') {
            Log::channel('single')->info('[AUDIT-FLOW] WAITING_DOCUMENT state - processing document directly', [
                'message' => $message,
                'context' => $context
            ]);
            return $this->handleWaitingDocument($phone, $message, $tenantId, $context, $tenant);
        }

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
                'override_reason' => 'None'
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
                'message' => 'Lo siento, tuve un problema técnico. ¿Podrías intentar de nuevo?',
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

            if ($systemInfo) {
                $secondAnalysis = $this->aiService->composeFromSystemResult(
                    $systemInfo,
                    $context,
                    $history
                );

                $aiMessage = $secondAnalysis['message'] ?? $aiMessage;
                $context   = $this->mergeExtracted($context, $secondAnalysis['data'] ?? []);
            } else {
                $aiMessage = $systemResult['message'] ?? $aiMessage;
            }
        }

        $history[] = ['role' => 'user',  'text' => $message];
        $history[] = ['role' => 'model', 'text' => $finalMessage];
        if (count($history) > 40) {
            $history = array_slice($history, -40);
        }

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

        if (!empty($systemFeedback)) {
            $finalMessage = $systemFeedback;
        } else {
            $finalMessage = $aiMessage ?: 'Lo siento, no estoy seguro de cómo ayudarte.';
        }

        Log::info('[AgentService] Final Response Decision', [
            'final_message' => $finalMessage,
            'last_action' => $action,
            'context_snapshot' => $context
        ]);

        $this->whatsAppService->sendText($phone, $finalMessage);

        return ['message' => $finalMessage];
    }

    private function handleWaitingDocument(string $phone, string $message, int $tenantId, array $context, ?Tenant $tenant): array
    {
        $tipoDocumento = $context['tipo_documento'] ?? null;
        $documento = $this->extractDocument($message);

        if (!$documento) {
            $finalMessage = "No pude entender tu respuesta. Por favor, indica tu número de documento (solo números, sin puntos ni comas).";
            $this->sendAndUpdateState($phone, $finalMessage, $context, $tenantId, null, $message);
            return ['message' => $finalMessage];
        }

        if (!$tipoDocumento) {
            $tipoDocumento = $this->extractDocumentType($message) ?? 'CC';
        }

        $this->conversationService->setState($phone, array_merge($context, [
            'documento' => $documento,
            'tipo_documento' => $tipoDocumento,
            'conversation_step' => 'VALIDATING_DOCUMENT',
            'tenant_id' => $tenantId,
        ]), $tenantId);

        $validation = $this->patientValidationService->validate($documento, $tenantId);

        if (!$validation['exists']) {
            $this->conversationService->setPatientNotFoundState($phone, $tenantId, $documento);

            $registerUrl = $this->patientValidationService->generateRegisterUrl($tenant, $documento, $phone);
            $context['registered'] = false;
            $context['patient_found'] = false;
            $context['conversation_step'] = 'PATIENT_NOT_FOUND';
            $context['telefono'] = $phone;

            $finalMessage = "No te encontré en nuestro sistema. Para registrarte, completa el formulario: {$registerUrl}";

            $this->sendAndUpdateState($phone, $finalMessage, $context, $tenantId, 'PATIENT_NOT_FOUND', $message);
            return ['message' => $finalMessage];
        }

        $patient = $validation['patient'];
        $this->patientValidationService->associatePhoneIfNeeded($patient, $phone);
        $this->conversationService->setPatientFoundState($phone, $tenantId, $patient->id, [
            'documento' => $patient->documento,
            'nombre' => $patient->nombre,
            'apellido' => $patient->apellido,
            'tipo_documento' => $patient->tipo_documento,
        ]);

        $servicesUrl = $this->patientValidationService->generateServicesUrl($tenant, $patient, $phone);

        $context['registered'] = true;
        $context['patient_id'] = $patient->id;
        $context['nombre'] = $patient->nombre;
        $context['apellido'] = $patient->apellido;
        $context['patient_found'] = true;
        $context['conversation_step'] = 'PATIENT_FOUND';
        $context['telefono'] = $phone;

        $finalMessage = "¡Hola {$patient->nombre}! Hemos identificado tu registro. Para seleccionar un servicio e iniciar tu solicitud de cita, ingresa al siguiente enlace: {$servicesUrl}";

        $this->sendAndUpdateState($phone, $finalMessage, $context, $tenantId, 'PATIENT_FOUND', $message);
        return ['message' => $finalMessage];
    }

    private function sendAndUpdateState(string $phone, string $message, array $context, int $tenantId, ?string $conversationStep = null, string $userMessage = ''): void
    {
        $history = $context['history'] ?? [];
        $history[] = ['role' => 'user', 'text' => $userMessage ?: ($context['ultimo_mensaje'] ?? '')];
        $history[] = ['role' => 'model', 'text' => $message];
        if (count($history) > 40) {
            $history = array_slice($history, -40);
        }

        $state = array_merge($context, [
            'history' => $history,
            'tenant_id' => $tenantId,
        ]);

        if ($conversationStep) {
            $state['conversation_step'] = $conversationStep;
        }

        $this->conversationService->setState($phone, $state, $tenantId);
        $this->whatsAppService->sendText($phone, $message);
    }

    private function generateSignedUrl(string $routeName, string $tenantSlug, array $params = []): string
    {
        $baseUrl = config('app.url');
        $tenantSlug = $tenantSlug ?: 'default';

        $url = URL::temporarySignedRoute(
            $routeName,
            now()->addDays(7),
            array_merge(['tenant' => $tenantSlug], $params)
        );

        return $url;
    }

    private function mergeAndResolveExtracted(array $context, array $extracted, int $tenantId, ?string &$systemFeedback): array
    {
        $entitiesToResolve = [
            'servicio_id'    => 'servicios',
            'profesional_id' => 'profesionales',
            'sede_id'        => 'sedes',
        ];

        foreach ($extracted as $key => $value) {
            if ($value === null || $value === '') continue;

            if (array_key_exists($key, $entitiesToResolve)) {
                $tableName = $entitiesToResolve[$key];
                $resolution = $this->entityResolutionService->resolve($tableName, $value, 'nombre', $tenantId);

                if ($resolution['status'] === 'success') {
                    $context[$key] = $resolution['id'];
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

    protected function executeAction(string $action, array $context, string $phone, string $message, int $tenantId): ?array
    {
        $tenantSlug = $context['tenant_slug'] ?? '';
        $tenant = Tenant::find($tenantId);
        $clinicName = $tenant?->nombre ?? 'nuestra clínica';

        switch ($action) {
            case 'ASK_DOCUMENT':
            case 'VALIDATE_PATIENT':
                $conversationStep = $context['conversation_step'] ?? null;

                if (!$context['documento'] && $conversationStep !== 'WAITING_DOCUMENT') {
                    $this->conversationService->setDocumentPendingState($phone, $tenantId, 'WAITING_DOCUMENT');
                    return [
                        'context' => array_merge($context, ['conversation_step' => 'WAITING_DOCUMENT', 'documento' => null]),
                        'message' => "¡Hola! Bienvenido a {$clinicName}. Para comenzar, necesito tu número de documento (sin puntos ni comas).",
                        'info' => null
                    ];
                }

                $documento = $context['documento'] ?? $this->extractDocument($message);

                if (!$documento && $conversationStep === 'WAITING_DOCUMENT') {
                    $this->conversationService->setDocumentPendingState($phone, $tenantId, 'WAITING_DOCUMENT');
                    return [
                        'context' => array_merge($context, ['conversation_step' => 'WAITING_DOCUMENT']),
                        'message' => "No pude entender tu respuesta. Por favor, indica tu número de documento (solo números, sin puntos ni comas).",
                        'info' => null
                    ];
                }

                if (!$documento) {
                    $this->conversationService->setDocumentPendingState($phone, $tenantId, 'WAITING_DOCUMENT');
                    return [
                        'context' => array_merge($context, ['conversation_step' => 'WAITING_DOCUMENT']),
                        'message' => "¡Hola! Bienvenido a {$clinicName}. Para comenzar, necesito tu número de documento (sin puntos ni comas).",
                        'info' => null
                    ];
                }

                $context['documento'] = $documento;
                $validation = $this->patientValidationService->validate($documento, $tenantId);

                if (!$validation['exists']) {
                    $this->conversationService->setPatientNotFoundState($phone, $tenantId, $documento);

                    $registerUrl = $this->patientValidationService->generateRegisterUrl($tenant, $documento, $phone);
                    $context['registered'] = false;
                    $context['patient_found'] = false;
                    $context['conversation_step'] = 'PATIENT_NOT_FOUND';

                    return [
                        'context' => $context,
                        'message' => "No te encontré en nuestro sistema. Para registrarte, completa el formulario: {$registerUrl}",
                        'info' => null
                    ];
                }

                $patient = $validation['patient'];
                $this->conversationService->setPatientFoundState($phone, $tenantId, $patient->id, [
                    'documento' => $patient->documento,
                    'nombre' => $patient->nombre,
                    'apellido' => $patient->apellido,
                ]);

                $servicesUrl = $this->patientValidationService->generateServicesUrl($tenant, $patient, $phone);

                $context['registered'] = true;
                $context['patient_id'] = $patient->id;
                $context['nombre'] = $patient->nombre;
                $context['apellido'] = $patient->apellido;
                $context['patient_found'] = true;
                $context['conversation_step'] = 'PATIENT_FOUND';

                return [
                    'context' => $context,
                    'message' => "¡Hola {$patient->nombre}! Hemos identificado tu registro. Para seleccionar un servicio e iniciar tu solicitud de cita, ingresa al siguiente enlace: {$servicesUrl}",
                    'info' => null
                ];

            case 'GET_SERVICES':
            case 'ASK_SERVICE':
                $response = $this->callController(AppointmentController::class, 'getServices', ['tenant_id' => $tenantId]);
                $dataArray = json_decode(json_encode($response['data'] ?? []), true);
                if (empty($dataArray)) return ['context' => $context, 'message' => 'No hay servicios disponibles.'];
                $lista = $this->formatList($dataArray, 'nombre');
                return ['context' => $context, 'info' => "Servicios: {$lista}."];

            case 'GET_PROFESSIONALS':
            case 'ASK_PROFESSIONAL':
                $response = $this->callController(AppointmentController::class, 'getProfessionals', [
                    'tenant_id' => $tenantId,
                    'servicio_id' => $context['servicio_id'] ?? null
                ]);
                $dataArray = json_decode(json_encode($response['data'] ?? []), true);
                $lista = $this->formatList($dataArray, 'nombre');
                return ['context' => $context, 'info' => "Profesionales: {$lista}."];

            case 'GET_SEDES':
                $response = $this->callController(AppointmentController::class, 'getSedes', ['tenant_id' => $tenantId]);
                $dataArray = json_decode(json_encode($response['data'] ?? []), true);
                if (empty($dataArray)) return ['context' => $context, 'message' => 'No hay sedes disponibles.'];
                if (count($dataArray) === 1) {
                    $context['sede_id'] = $dataArray[0]['id'];
                    return ['context' => $context, 'info' => "Sede: {$dataArray[0]['nombre']}."];
                }
                $lista = $this->formatList($dataArray, 'nombre');
                return ['context' => $context, 'info' => "Sedes: {$lista}."];

            case 'GET_AVAILABILITY':
                $fecha = $context['fecha'] ?? null;
                $profesionalId = $context['profesional_id'] ?? null;
                if (!$fecha || !$profesionalId) return ['context' => $context, 'message' => 'Necesito fecha y profesional.'];
                $response = $this->callController(AppointmentController::class, 'getAvailability', [
                    'fecha' => $fecha,
                    'profesional_id' => $profesionalId,
                    'tenant_id' => $tenantId
                ]);
                $slots = $response['data']['available_slots'] ?? [];
                $info = empty($slots) ? "Sin horarios para {$fecha}." : "Horarios: " . implode(', ', $slots);
                return ['context' => $context, 'info' => $info];

            case 'CHECK_APPOINTMENTS':
                $documento = $context['documento'] ?? null;
                if (!$documento) return ['context' => $context, 'message' => 'Dime tu número de documento.'];
                $response = $this->callController(AppointmentController::class, 'getPatientAppointments', [
                    'documento' => $documento,
                    'tenant_id' => $tenantId
                ]);
                $citas = $response['data'] ?? [];
                if (empty($citas)) return ['context' => $context, 'info' => 'No tienes citas próximas.'];
                $lista = "";
                foreach($citas as $c) {
                    $lista .= "[{$c['id']}] " . date('d/m H:i', strtotime($c['fecha_hora'])) . " con {$c['profesional']}\n";
                }
                return ['context' => $context, 'info' => "Tus citas:\n{$lista}"];

            case 'CREATE_APPOINTMENT':
                $appointmentUrl = $this->generateSignedUrl('public.appointment', $tenantSlug, [
                    'phone' => $phone,
                    'documento' => $context['documento'] ?? null,
                ]);
                return [
                    'context' => $context,
                    'message' => "Para agendar tu cita, ingresa al formulario: {$appointmentUrl}",
                    'info' => null
                ];

            case 'FAQ':
                return [
                    'context' => $context,
                    'message' => "Solo puedo ayudarte con citas médicas en {$clinicName}. ¿Deseas agendar una cita?",
                    'info' => null
                ];

            case 'REGISTER_PATIENT':
                $registerUrl = $this->generateSignedUrl('public.patient.register', $tenantSlug, [
                    'phone' => $phone,
                ]);
                return [
                    'context' => $context,
                    'message' => "Para registrarte, completa el formulario: {$registerUrl}",
                    'info' => null
                ];

            default:
                return null;
        }
    }

    private function buildContext(array $rawState, int $tenantId, string $clinicName): array
    {
        $data = $rawState['data'] ?? [];
        $tenant = Tenant::find($tenantId);

        return [
            'conversation_step' => $rawState['conversation_step'] ?? null,
            'patient_found' => $rawState['patient_found'] ?? false,
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
            'tenant_slug' => $tenant?->slug ?? '',
        ];
    }

    private function extractDocument(string $message): ?string
    {
        if (preg_match('/\b(\d{5,15})\b/', $message, $matches)) {
            return $matches[1];
        }
        return null;
    }

    private function extractDocumentType(string $message): ?string
    {
        $types = ['CC', 'TI', 'CE', 'PA', 'PASAPORTE', 'CÉDULA'];

        foreach ($types as $type) {
            if (stripos($message, $type) !== false) {
                return strtoupper($type) === 'CÉDULA' ? 'CC' : strtoupper($type);
            }
        }

        return null;
    }

    private function formatList(array $items, string $labelField): string
    {
        if (empty($items)) return 'ninguno';
        return implode(", ", array_map(fn($item) => $item[$labelField], $items));
    }

    protected function callController(string $controllerClass, string $method, array $params = []): array
    {
        $controller = app($controllerClass);
        $request = new Request();
        $request->merge($params);
        $response = $controller->$method($request);

        if (!$response || !method_exists($response, 'getContent')) {
            Log::error("[AgentService] Controller {$controllerClass}::{$method} invalid response.");
            return [];
        }

        return json_decode($response->getContent(), true) ?? [];
    }
}