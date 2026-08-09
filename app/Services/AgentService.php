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
        protected AIServiceInterface $aiService,
        protected ConversationService $conversationService,
        protected WhatsAppService $whatsAppService,
        protected EntityResolutionService $entityResolutionService,
        protected PatientValidationService $patientValidationService,
        protected ShortLinkService $shortLinkService
    ) {
    }

    public function handleMessage(string $phone, string $message, int $tenantId): array
    {
        Log::channel('single')->info('═══════════════════════════════════════════════════════════════', []);
        Log::channel('single')->info('[STEP 1] INICIO - Nuevo mensaje recibido', [
            'phone' => $phone,
            'message' => $message,
            'tenant_id' => $tenantId,
        ]);
        Log::channel('single')->info('═══════════════════════════════════════════════════════════════', []);

        $finalMessage = '';

        $tenant = Tenant::find($tenantId);
        $clinicName = $tenant ? $tenant->nombre : 'nuestra clínica';

        Log::channel('single')->info('[STEP 2] Tenant identificado', [
            'tenant_id' => $tenantId,
            'tenant_nombre' => $clinicName,
            'tenant_slug' => $tenant?->slug,
        ]);

        Log::channel('single')->info('[STEP 3] Obteniendo estado de conversación desde BD', [
            'phone' => $phone,
            'tenant_id' => $tenantId,
        ]);

        $rawState = $this->conversationService->getState($phone, $tenantId);
        $context = $this->buildContext($rawState, $tenantId, $clinicName);

        Log::channel('single')->info('[STEP 4] Estado de conversación obtenido', [
            'phone' => $phone,
            'conversation_step' => $context['conversation_step'] ?? 'null',
            'patient_found' => $context['patient_found'] ?? false,
            'has_document' => !empty($context['documento']),
            'state_raw' => $rawState,
        ]);

        if (empty($context['telefono'])) {
            $context['telefono'] = $phone;
        }

        $history = $rawState['history'] ?? [];
        $context['clinic_name'] = $clinicName;
        $context['tenant_slug'] = $tenant?->slug ?? '';

        $conversationStep = $context['conversation_step'] ?? null;

        Log::channel('single')->info('[STEP 5] Verificando paso de conversación', [
            'conversation_step' => $conversationStep,
            'is_waiting_document' => ($conversationStep === 'WAITING_DOCUMENT'),
        ]);

        /**
         * REGLA: Si el mensaje parece ser un saludo o no es un documento válido,
         * Y el conversation_step NO es WAITING_DOCUMENT,
         * entonces reiniciar el flujo con un saludo.
         *
         * Esto evita que paciente quede atrapado en PATIENT_NOT_FOUND o PATIENT_FOUND
         */
        $isDocumentMessage = $this->isDocumentMessage($message);
        $isGreetingMessage = $this->isGreetingMessage($message);

        Log::channel('single')->info('[STEP 5a] Clasificando mensaje', [
            'message' => $message,
            'is_document' => $isDocumentMessage,
            'is_greeting' => $isGreetingMessage,
            'conversation_step' => $conversationStep,
        ]);

        if ($conversationStep === 'WAITING_DOCUMENT') {
            Log::channel('single')->info('[STEP 6] Estado WAITING_DOCUMENT - procesando documento directamente', [
                'message' => $message,
                'context' => $context,
            ]);
            return $this->handleWaitingDocument($phone, $message, $tenantId, $context, $tenant);
        }

        Log::channel('single')->info('[STEP 6] Enviando mensaje a IA para análisis', [
            'message' => $message,
            'history_length' => count($history),
            'context' => $context,
        ]);

        $analysis = $this->aiService->analyzeMessage($message, $context, $history);
        $intent = $analysis['intent'] ?? 'UNKNOWN';
        $action = $analysis['action'] ?? 'UNKNOWN';
        $aiMessage = $analysis['message'] ?? '';
        $extractedData = $analysis['data'] ?? [];

        Log::channel('single')->info('[STEP 7] Respuesta de IA recibida', [
            'intent' => $intent,
            'action' => $action,
            'ai_message' => $aiMessage,
            'extracted_data' => $extractedData,
        ]);

        $systemFeedback = null;
        $context = $this->mergeAndResolveExtracted($context, $extractedData, $tenantId, $systemFeedback);

        Log::channel('single')->info('[STEP 8] Contexto fusionado y resolved', [
            'context_after_merge' => $context,
            'system_feedback' => $systemFeedback,
        ]);

        Log::channel('single')->info('[STEP 9] Ejecutando acción', [
            'action' => $action,
            'tenant_id' => $tenantId,
        ]);

        try {
            $systemResult = $this->executeAction($action, $context, $phone, $message, $tenantId);
        } catch (\Exception $e) {
            Log::channel('single')->error('[STEP 9] ERROR en executeAction', [
                'action' => $action,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $systemResult = [
                'context' => $context,
                'message' => 'Lo siento, tuve un problema técnico. ¿Podrías intentar de nuevo?',
            ];
        }

        Log::channel('single')->info('[STEP 10] Resultado de executeAction', [
            'action' => $action,
            'system_result' => $systemResult,
        ]);

        if ($systemResult !== null) {
            $context = $systemResult['context'];
            $systemInfo = $systemResult['info'] ?? '';

            if ($systemInfo) {
                Log::channel('single')->info('[STEP 11] Sistema generó info adicional, reenviando a IA', [
                    'system_info' => $systemInfo,
                ]);

                $secondAnalysis = $this->aiService->composeFromSystemResult(
                    $systemInfo,
                    $context,
                    $history
                );

                $aiMessage = $secondAnalysis['message'] ?? $aiMessage;
                $context = $this->mergeExtracted($context, $secondAnalysis['data'] ?? []);

                Log::channel('single')->info('[STEP 12] Segunda respuesta de IA', [
                    'ai_message' => $aiMessage,
                ]);
            } else {
                $aiMessage = $systemResult['message'] ?? $aiMessage;
            }
        }

        $history[] = ['role' => 'user', 'text' => $message];
        $history[] = ['role' => 'model', 'text' => $finalMessage];
        if (count($history) > 40) {
            $history = array_slice($history, -40);
        }

        Log::channel('single')->info('[STEP 13] Guardando estado en BD', [
            'final_context' => $context,
            'intent' => $intent,
            'step' => $action,
        ]);

        $this->conversationService->setState($phone, array_merge($context, [
            'history' => $history,
            'intent' => $intent,
            'step' => $action,
            'tenant_id' => $tenantId,
        ]), $tenantId);

        if (!empty($systemFeedback)) {
            $finalMessage = $systemFeedback;
        } else {
            $finalMessage = $aiMessage ?: 'Lo siento, no estoy seguro de cómo ayudarte.';
        }

        Log::channel('single')->info('[STEP 14] Mensaje final a enviar', [
            'final_message' => $finalMessage,
            'phone' => $phone,
        ]);

        $this->whatsAppService->forTenant($tenantId)->sendText($phone, $finalMessage);

        Log::channel('single')->info('[STEP 15] Mensaje enviado - Proceso completado', [
            'phone' => $phone,
            'action' => $action,
            'intent' => $intent,
        ]);
        Log::channel('single')->info('═══════════════════════════════════════════════════════════════', []);

        return ['message' => $finalMessage];
    }

    private function handleWaitingDocument(string $phone, string $message, int $tenantId, array $context, ?Tenant $tenant): array
    {
        Log::channel('single')->info('[WAITING_DOCUMENT] Iniciando procesamiento de documento', [
            'phone' => $phone,
            'message' => $message,
            'tenant_id' => $tenantId,
        ]);

        $tipoDocumento = $context['tipo_documento'] ?? null;
        $documento = $this->extractDocument($message);

        if (!$documento) {
            Log::channel('single')->info('[WAITING_DOCUMENT] No se pudo extraer documento del mensaje', [
                'message' => $message,
                'tipo_documento' => $tipoDocumento,
            ]);

            $finalMessage = "No pude entender tu respuesta. Por favor, indica tu número de documento (solo números, sin puntos ni comas).";
            $this->sendAndUpdateState($phone, $finalMessage, $context, $tenantId, null, $message);
            return ['message' => $finalMessage];
        }

        Log::channel('single')->info('[WAITING_DOCUMENT] Documento extraído', [
            'documento' => $documento,
            'tipo_documento' => $tipoDocumento,
        ]);

        if (!$tipoDocumento) {
            $tipoDocumento = $this->extractDocumentType($message) ?? 'CC';
            Log::channel('single')->info('[WAITING_DOCUMENT] Tipo de documento inferido', [
                'tipo_documento' => $tipoDocumento,
            ]);
        }

        $this->conversationService->setState($phone, array_merge($context, [
            'documento' => $documento,
            'tipo_documento' => $tipoDocumento,
            'conversation_step' => 'VALIDATING_DOCUMENT',
            'tenant_id' => $tenantId,
        ]), $tenantId);

        Log::channel('single')->info('[WAITING_DOCUMENT] Validando paciente en BD', [
            'documento' => $documento,
            'tipo_documento' => $tipoDocumento,
            'tenant_id' => $tenantId,
        ]);

        $validation = $this->patientValidationService->validate($documento, $tenantId);

        if (!$validation['exists']) {
            Log::channel('single')->info('[WAITING_DOCUMENT] Paciente NO encontrado', [
                'documento' => $documento,
                'tenant_id' => $tenantId,
            ]);

            $this->conversationService->setPatientNotFoundState($phone, $tenantId, $documento);

            $registerUrl = $this->patientValidationService->generateRegisterUrl($tenant, $documento, $phone);
            $context['registered'] = false;
            $context['patient_found'] = false;
            $context['conversation_step'] = 'PATIENT_NOT_FOUND';
            $context['telefono'] = $phone;

            $finalMessage = "Hola.\n\nNo encontramos un paciente registrado con ese documento.\n\nPara continuar con tu solicitud debemos completar tu registro.\n\nHaz clic en el siguiente enlace para registrarte:";

            Log::channel('single')->info('[WAITING_DOCUMENT] Enviando mensaje de paciente no encontrado', [
                'register_url' => $registerUrl,
            ]);

            $this->sendAndUpdateStateWithButton(
                $phone,
                $finalMessage,
                'Continuar',
                'btn_register_' . time(),
                $registerUrl,
                $context,
                $tenantId,
                'PATIENT_NOT_FOUND',
                $message
            );
            return ['message' => $finalMessage];
        }

        $patient = $validation['patient'];
        Log::channel('single')->info('[WAITING_DOCUMENT] Paciente ENCONTRADO', [
            'patient_id' => $patient->id,
            'nombre' => $patient->nombre,
            'documento' => $patient->documento,
        ]);

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

        $finalMessage = "Perfecto.\n\nEncontramos tu información correctamente.\n\nAhora puedes continuar con el proceso de agendamiento.\n\nHaz clic en el siguiente enlace:";

        Log::channel('single')->info('[WAITING_DOCUMENT] Enviando mensaje de paciente encontrado', [
            'services_url' => $servicesUrl,
        ]);

        $this->sendAndUpdateStateWithButton(
            $phone,
            $finalMessage,
            'Continuar',
            'btn_continue_' . time(),
            $servicesUrl,
            $context,
            $tenantId,
            'PATIENT_FOUND',
            $message
        );
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
        $this->whatsAppService->forTenant($tenantId)->sendText($phone, $message);
    }

    private function sendAndUpdateStateWithButton(string $phone, string $message, string $buttonText, string $buttonId, string $url, array $context, int $tenantId, ?string $conversationStep = null, string $userMessage = ''): void
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

        $shortUrl = $this->shortLinkService->getUrl($url, $tenantId);

        $sent = $this->whatsAppService->forTenant($tenantId)->sendInteractiveButtons($phone, $message, $buttonText, $buttonId, $shortUrl);

        if (!$sent) {
            Log::channel('single')->warning('[AgentService] Interactive button failed, falling back to text message');
            $this->whatsAppService->forTenant($tenantId)->sendText($phone, "{$message}\n\n{$shortUrl}");
        }
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
        Log::channel('single')->info('[EXECUTE_ACTION] Iniciando ejecución', [
            'action' => $action,
            'phone' => $phone,
            'tenant_id' => $tenantId,
            'context' => $context,
        ]);

        $tenantSlug = $context['tenant_slug'] ?? '';
        $tenant = Tenant::find($tenantId);
        $clinicName = $tenant?->nombre ?? 'nuestra clínica';

        switch ($action) {
            case 'ASK_DOCUMENT':
            case 'VALIDATE_PATIENT':
                Log::channel('single')->info('[EXECUTE_ACTION] Caso ASK_DOCUMENT/VALIDATE_PATIENT', []);

                $conversationStep = $context['conversation_step'] ?? null;

                if (!$context['documento'] && $conversationStep !== 'WAITING_DOCUMENT') {
                    Log::channel('single')->info('[EXECUTE_ACTION] No hay documento, solicitando', [
                        'conversation_step' => $conversationStep,
                    ]);

                    $this->conversationService->setDocumentPendingState($phone, $tenantId, 'WAITING_DOCUMENT');
                    return [
                        'context' => array_merge($context, ['conversation_step' => 'WAITING_DOCUMENT', 'documento' => null]),
                        'message' => "¡Hola! Bienvenido a {$clinicName}. Para comenzar, necesito tu número de documento (sin puntos ni comas).",
                        'info' => null
                    ];
                }

                $documento = $context['documento'] ?? $this->extractDocument($message);

                if (!$documento && $conversationStep === 'WAITING_DOCUMENT') {
                    Log::channel('single')->info('[EXECUTE_ACTION] WAITING_DOCUMENT pero no se extrajo documento', []);

                    $this->conversationService->setDocumentPendingState($phone, $tenantId, 'WAITING_DOCUMENT');
                    return [
                        'context' => array_merge($context, ['conversation_step' => 'WAITING_DOCUMENT']),
                        'message' => "No pude entender tu respuesta. Por favor, indica tu número de documento (solo números, sin puntos ni comas).",
                        'info' => null
                    ];
                }

                if (!$documento) {
                    Log::channel('single')->info('[EXECUTE_ACTION] Solicitarando documento por primera vez', []);

                    $this->conversationService->setDocumentPendingState($phone, $tenantId, 'WAITING_DOCUMENT');
                    return [
                        'context' => array_merge($context, ['conversation_step' => 'WAITING_DOCUMENT']),
                        'message' => "¡Hola! Bienvenido a {$clinicName}. Para comenzar, necesito tu número de documento (sin puntos ni comas).",
                        'info' => null
                    ];
                }

                $context['documento'] = $documento;
                Log::channel('single')->info('[EXECUTE_ACTION] Documento obtenido, validando paciente', [
                    'documento' => $documento,
                ]);

                $validation = $this->patientValidationService->validate($documento, $tenantId);

                if (!$validation['exists']) {
                    Log::channel('single')->info('[EXECUTE_ACTION] Paciente NO encontrado', [
                        'documento' => $documento,
                    ]);

                    $this->conversationService->setPatientNotFoundState($phone, $tenantId, $documento);

                    $registerUrl = $this->patientValidationService->generateRegisterUrl($tenant, $documento, $phone);
                    $shortUrl = $this->shortLinkService->getUrl($registerUrl, $tenantId);
                    $context['registered'] = false;
                    $context['patient_found'] = false;
                    $context['conversation_step'] = 'PATIENT_NOT_FOUND';

                    return [
                        'context' => $context,
                        'message' => "Hola.\n\nNo encontramos un paciente registrado con ese documento.\n\nPara continuar con tu solicitud debemos completar tu registro.\n\nHaz clic en el siguiente enlace para registrarte:\n\n🔗 {$shortUrl}",
                        'info' => null
                    ];
                }

                $patient = $validation['patient'];
                Log::channel('single')->info('[EXECUTE_ACTION] Paciente ENCONTRADO', [
                    'patient_id' => $patient->id,
                    'nombre' => $patient->nombre,
                ]);

                $this->conversationService->setPatientFoundState($phone, $tenantId, $patient->id, [
                    'documento' => $patient->documento,
                    'nombre' => $patient->nombre,
                    'apellido' => $patient->apellido,
                ]);

                $servicesUrl = $this->patientValidationService->generateServicesUrl($tenant, $patient, $phone);
                    $shortUrl = $this->shortLinkService->getUrl($servicesUrl, $tenantId);

                    $context['registered'] = true;
                    $context['patient_id'] = $patient->id;
                    $context['nombre'] = $patient->nombre;
                    $context['apellido'] = $patient->apellido;
                    $context['patient_found'] = true;
                    $context['conversation_step'] = 'PATIENT_FOUND';

                    return [
                        'context' => $context,
                        'message' => "Perfecto.\n\nEncontramos tu información correctamente.\n\nAhora puedes continuar con el proceso de agendamiento.\n\nHaz clic en el siguiente enlace:\n\n🔗 {$shortUrl}",
                        'info' => null
                    ];

            case 'GET_SERVICES':
            case 'ASK_SERVICE':
                Log::channel('single')->info('[EXECUTE_ACTION] Caso GET_SERVICES', []);

                $response = $this->callController(AppointmentController::class, 'getServices', ['tenant_id' => $tenantId]);
                $dataArray = json_decode(json_encode($response['data'] ?? []), true);
                if (empty($dataArray)) {
                    Log::channel('single')->info('[EXECUTE_ACTION] No hay servicios disponibles', []);
                    return ['context' => $context, 'message' => 'No hay servicios disponibles.'];
                }
                $lista = $this->formatList($dataArray, 'nombre');
                return ['context' => $context, 'info' => "Servicios: {$lista}."];

            case 'GET_PROFESSIONALS':
            case 'ASK_PROFESSIONAL':
                Log::channel('single')->info('[EXECUTE_ACTION] Caso GET_PROFESSIONALS', []);

                $response = $this->callController(AppointmentController::class, 'getProfessionals', [
                    'tenant_id' => $tenantId,
                    'servicio_id' => $context['servicio_id'] ?? null
                ]);
                $dataArray = json_decode(json_encode($response['data'] ?? []), true);
                $lista = $this->formatList($dataArray, 'nombre');
                return ['context' => $context, 'info' => "Profesionales: {$lista}."];

            case 'GET_SEDES':
                Log::channel('single')->info('[EXECUTE_ACTION] Caso GET_SEDES', []);

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
                Log::channel('single')->info('[EXECUTE_ACTION] Caso GET_AVAILABILITY', []);

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
                Log::channel('single')->info('[EXECUTE_ACTION] Caso CHECK_APPOINTMENTS', []);

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
                Log::channel('single')->info('[EXECUTE_ACTION] Caso CREATE_APPOINTMENT', []);

                $appointmentUrl = $this->generateSignedUrl('public.appointment', $tenantSlug, [
                    'phone' => $phone,
                    'documento' => $context['documento'] ?? null,
                ]);
                $shortUrl = $this->shortLinkService->getUrl($appointmentUrl, $tenantId);
                return [
                    'context' => $context,
                    'message' => "Para agendar tu cita, ingresa al formulario:\n\n🔗 {$shortUrl}",
                    'info' => null
                ];

            case 'FAQ':
                Log::channel('single')->info('[EXECUTE_ACTION] Caso FAQ', []);

                return [
                    'context' => $context,
                    'message' => "Solo puedo ayudarte con citas médicas en {$clinicName}. ¿Deseas agendar una cita?",
                    'info' => null
                ];

            case 'REGISTER_PATIENT':
                Log::channel('single')->info('[EXECUTE_ACTION] Caso REGISTER_PATIENT', []);

                $registerUrl = $this->generateSignedUrl('public.patient.register', $tenantSlug, [
                    'phone' => $phone,
                ]);
                $shortUrl = $this->shortLinkService->getUrl($registerUrl, $tenantId);
                return [
                    'context' => $context,
                    'message' => "Para registrarte, completa el formulario:\n\n🔗 {$shortUrl}",
                    'info' => null
                ];

            default:
                Log::channel('single')->info('[EXECUTE_ACTION] Acción desconocida', [
                    'action' => $action,
                ]);
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

    private function isDocumentMessage(string $message): bool
    {
        $message = trim($message);
        $patterns = [
            '/^CC\s*\d{5,15}$/i',
            '/^TI\s*\d{5,15}$/i',
            '/^CE\s*\d{5,15}$/i',
            '/^PA\s*\d{5,15}$/i',
            '/^PASAPORTE\s*\d{5,15}$/i',
            '/^\d{5,15}$/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $message)) {
                return true;
            }
        }

        return false;
    }

    private function isGreetingMessage(string $message): bool
    {
        $greetings = [
            'hola', 'buenos', 'buenas', 'buen día', 'buenas tardes', 'buenas noches',
            'saludos', 'saludo', 'hello', 'hi', 'hey', 'qué tal', 'como estas',
            'cómo estás', 'buen día', 'que tal', 'hola que tal', 'hola como estas',
            'necesito una cita', 'quiero una cita', 'agendar cita', 'solicitar cita',
            'pedir cita', 'necesito', 'quiero', 'ayuda', 'help', 'por favor',
        ];

        $message = strtolower(trim($message));

        foreach ($greetings as $greeting) {
            if (strpos($message, $greeting) !== false) {
                return true;
            }
        }

        return false;
    }
}