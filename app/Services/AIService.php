<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class AIService
{
    private string $apiKey;
    private string $model;
    private string $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/';

    // Acciones válidas que el modelo puede devolver
    private const VALID_INTENTS = [
        'REGISTER_PATIENT',
        'CREATE_APPOINTMENT',
        'GET_SERVICES',
        'GET_PROFESSIONALS',
        'GET_SEDES',
        'GET_AVAILABILITY',
        'FAQ',
        'UNKNOWN',
    ];

    private const VALID_ACTIONS = [
        'ASK_DOC_TYPE',
        'ASK_NAME',
        'ASK_DOCUMENT',
        'ASK_PHONE',
        'ASK_SERVICE',
        'ASK_PROFESSIONAL',
        'ASK_DATE',
        'ASK_TIME',
        'GET_SERVICES',
        'GET_PROFESSIONALS',
        'GET_SEDES',
        'REGISTER_PATIENT',
        'CONFIRM_PATIENT_DATA',
        'CREATE_APPOINTMENT',
        'VALIDATE_PATIENT',
        'GET_AVAILABILITY',
        'CHECK_APPOINTMENTS',
        'MODIFY_APPOINTMENT',
        'FAQ',
        'UNKNOWN',
    ];

    // Keys válidas que pueden venir en data{}
    private const VALID_DATA_KEYS = [
        'tipo_documento',
        'documento',
        'nombre',
        'apellido',
        'telefono',
        'servicio_id',
        'profesional_id',
        'sede_id',
        'cita_id',
        'fecha',
        'hora',
        'fecha_hora',
        'consentimiento',
    ];

    // Schema mínimo de contexto con sus defaults
    private const CONTEXT_DEFAULTS = [
        'registered' => false,
        'tipo_documento' => null,
        'documento' => null,
        'nombre' => null,
        'apellido' => null,
        'telefono' => null,
        'servicio_id' => null,
        'profesional_id' => null,
        'sede_id' => null,
        'fecha' => null,
        'hora' => null,
        'consentimiento' => false,
        'clinic_name' => 'nuestra clínica',
    ];

    public function __construct()
    {
        $this->apiKey = config('services.gemini.key');
        $this->model = config('services.gemini.model');
    }

    public function analyzeMessage(string $message, array $context = [], array $history = []): array
    {
        $context = $this->normalizeContext($context);
        $payload = $this->buildPayload($message, $context, $history);

        try {
            \Illuminate\Support\Facades\Log::info('[AUDIT-3] Sending request to Gemini (analyzeMessage)', [
                'model'   => $this->model,
                'payload' => $payload, // Key is in URL, not in payload body
            ]);

            $response = Http::retry(3, 800, function ($exception) {
                return !($exception instanceof \Illuminate\Http\Client\RequestException
                    && $exception->response->status() < 500);
            })->post(
                "{$this->apiUrl}{$this->model}:generateContent?key={$this->apiKey}",
                $payload
            );

            \Illuminate\Support\Facades\Log::info('[AUDIT-3] Gemini response received (analyzeMessage)', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            if ($response->failed()) {
                throw new Exception("Gemini API error [{$response->status()}]: " . $response->body());
            }

            $result = $response->json();
            return $this->parseAndValidate($result, $context);

        } catch (Exception $e) {
            \Illuminate\Support\Facades\Log::error('[AUDIT-3] Gemini Error (analyzeMessage): ' . $e->getMessage());
            return $this->fallbackResponse();
        }
    }

    public function composeFromSystemResult(
        string $systemInfo,
        array $context = [],
        array $history = []
    ): array {
        $context = $this->normalizeContext($context);
        $payload = $this->buildPayloadWithSystemResult($systemInfo, $context, $history);

        try {
            \Illuminate\Support\Facades\Log::info('[AUDIT-3] Sending request to Gemini (composeFromSystemResult)', [
                'model'   => $this->model,
                'payload' => $payload,
            ]);

            $response = Http::retry(3, 800, function ($exception) {
                return !($exception instanceof \Illuminate\Http\Client\RequestException
                    && $exception->response->status() < 500);
            })->post(
                "{$this->apiUrl}{$this->model}:generateContent?key={$this->apiKey}",
                $payload
            );

            \Illuminate\Support\Facades\Log::info('[AUDIT-3] Gemini response received (composeFromSystemResult)', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            if ($response->failed()) {
                throw new Exception("Gemini API error [{$response->status()}]: " . $response->body());
            }

            $result = $response->json();
            return $this->parseAndValidate($result, $context);

        } catch (Exception $e) {
            \Illuminate\Support\Facades\Log::error('[AUDIT-3] Gemini Error (composeFromSystemResult): ' . $e->getMessage());
            return $this->fallbackResponse();
        }
    }

    private function buildPayload(string $message, array $context, array $history): array
    {
        $contents = [];
        
        // 1. Inyectar el Estado Actual como la primera pieza de información del usuario
        $filledContext = array_filter($context, fn($v) => $v !== null && $v !== false);
        $contents[] = [
            'role'  => 'user', 
            'parts' => [['text' => "[SISTEMA - ESTADO ACTUAL DEL USUARIO]: " . json_encode($filledContext, JSON_UNESCAPED_UNICODE) . "\nUtiliza este estado para decidir el siguiente paso. No pidas datos que ya estén aquí."]]
        ];

        // 2. Agregar el historial de conversación natural
        foreach ($history as $turn) {
            if (!isset($turn['role'], $turn['text'])) continue;
            $contents[] = ['role'  => $turn['role'], 'parts' => [['text' => $turn['text']]]];
        }

        // 3. El mensaje actual del usuario
        $contents[] = ['role'  => 'user', 'parts' => [['text' => $message]]];
        
        return $this->wrapPayload($contents);
    }
    private function buildPayloadWithSystemResult(string $systemInfo, array $context, array $history): array
    {
        $contents = [];
        
        // 1. Inyectar el Estado Actual
        $filledContext = array_filter($context, fn($v) => $v !== null && $v !== false);
        $contents[] = [
            'role'  => 'user', 
            'parts' => [['text' => "[SISTEMA - ESTADO ACTUAL DEL USUARIO]: " . json_encode($filledContext, JSON_UNESCAPED_UNICODE)]]
        ];

        // 2. Agregar el historial
        foreach ($history as $turn) {
            if (!isset($turn['role'], $turn['text'])) continue;
            $contents[] = ['role'  => $turn['role'], 'parts' => [['text' => $turn['text']]]];
        }

        // 3. Resultado del sistema y la instrucción de redacción
        $contents[] = ['role'  => 'model', 'parts' => [['text' => '[RESULTADO_SISTEMA] ' . $systemInfo]]];
        $contents[] = ['role'  => 'user', 'parts' => [['text' => 'Redacta la respuesta al usuario basándote en [RESULTADO_SISTEMA]. Luego, analiza el [ESTADO_ACTUAL] y decide la siguiente acción siguiendo la secuencia lineal obligatoria: Servicio -> Profesional -> Sede -> Fecha -> Disponibilidad. Tienes PROHIBIDO retroceder a un paso que ya tenga un valor asignado en el [ESTADO_ACTUAL]. Sé breve y directo.']]];
        
        return $this->wrapPayload($contents);
    }


    private function wrapPayload(array $contents): array
    {
        return [
            'system_instruction' => ['parts' => [['text' => $this->buildSystemPrompt()]]],
            'contents'         => $contents,
            'generationConfig' => [
                'temperature'        => 0.0,
                'topP'               => 0.8,
                'maxOutputTokens'    => 1024,
                'response_mime_type' => 'application/json',
                'response_schema'    => $this->responseSchema(),
            ],
        ];
    }

    private function buildSystemPrompt(): string
    {
        return <<<'PROMPT'
Eres el recepcionista virtual de una clínica de podología. Tu única función es guiar al usuario para agendar una cita médica. No puedes hacer nada fuera de ese alcance.
Saluida siempre mencionando el nombre de la clínica proporcionado en el contexto (`clinic_name`).
DEBES responder SIEMPRE y EXCLUSIVAMENTE en idioma español. Nunca mezcles idiomas ni respondan en inglés.

════════════════════════════════════════
REGLAS ABSOLUTAS (nunca las violes)
════════════════════════════════════════
1. Responde EXCLUSIVAMENTE con el objeto JSON definido en el schema. Sin texto adicional, sin markdown.
2. Nunca inventes datos: si no tienes un dato, su valor en `data` debe ser null.
3. Nunca confundas lo que el usuario DICE con lo que el sistema TIENE. El estado real está en [ESTADO_ACTUAL].
4. Si el usuario envía una respuesta muy corta (ej. un número o una palabra), extrae el dato correspondiente en `data` y decide la acción basándote en el estado actual. No generes texto innecesario.
5. Si el usuario pide algo fuera del alcance (clima, recetas, chistes, etc.) → intent: FAQ, action: FAQ, mensaje explicando que solo puedes agendar citas.
6. Si el usuario insulta o escribe algo inapropiado → intent: UNKNOWN, action: UNKNOWN, mensaje cortés pidiendo que reformule.
7. NUNCA digas "estoy verificando", "dame un momento" ni simules acciones del sistema. El sistema ejecuta las acciones; tú solo decides cuál es la siguiente.
8. MANTÉN la comunicación estrictamente en español.


════════════════════════════════════════
CÓMO LEER EL ESTADO
════════════════════════════════════════
El mensaje con prefijo [ESTADO_ACTUAL] contiene el JSON con los datos que ya se tienen.
- registered: true → el paciente ya existe en el sistema.
- Cualquier campo con valor = ese dato ya fue recopilado y no debes volver a pedirlo.

════════════════════════════════════════
EXTRACCIÓN DE DATOS (Súper Crítico)
════════════════════════════════════════
Cuando el usuario proporcione uno o VARIOS datos en su mensaje, DEBES extraer TODOS ellos simultáneamente en el objeto `data`. 

REGLA DE EXTRACCIÓN MÚLTIPLE:
No te limites a extraer el dato que solicitaste. Escucha todo el mensaje. Si el usuario dice "Quiero podología con Juan el lunes a las 10", debes extraer:
- servicio_id (o nombre para resolución): "podología"
- profesional_id (o nombre para resolución): "Juan"
- fecha: "lunes" (convertido a YYYY-MM-DD si es posible, o literal)
- hora: "10:00"

REGLA DE ORO PARA RESPUESTAS CORTAS:
Si el usuario responde con un solo número (ej. "1") o un nombre corto, este dato pertenece OBLIGATORIAMENTE a la entidad que solicitaste en el mensaje inmediatamente anterior.
- Si pediste Servicio → el "1" es `servicio_id`.
- Si pediste Profesional → el "1" es `profesional_id`.
- Si pediste Sede → el "1" es `sede_id`.
NUNCA asignes un número a una entidad diferente a la que acabas de solicitar.

REGLA DE ORO PARA IDs:
Para `servicio_id`, `profesional_id` y `sede_id`, intenta extraer el ID numérico. Si el usuario proporciona un nombre, pon el nombre en el campo correspondiente; el sistema se encargará de resolverlo al ID numérico.
- Ejemplo: "Con Juan" → data: {"profesional_id": "Juan"}
- Ejemplo: "Servicio 5" → data: {"servicio_id": "5"}

REGLA PARA FECHAS Y HORAS:
- Extrae la fecha y la hora exactamente como las proporcione el usuario.
- Formato preferido fecha: YYYY-MM-DD.
- Formato preferido hora: HH:mm.

Ejemplos:
  Usuario: "Mi cédula es 123456"     → data: {"documento": "123456"}
  Usuario: "Me llamo Carlos Ruiz"    → data: {"nombre": "Carlos", "apellido": "Ruiz"}
  Usuario: "Mi celular es 3001234567"→ data: {"telefono": "3001234567"}
  Usuario: "Quiero el servicio 2"    → data: {"servicio_id": "2"}
  Usuario: "Para el 2025-08-15"      → data: {"fecha": "2025-08-15"}

Solo incluye en `data` los campos que el usuario mencionó EN ESTE MENSAJE. No repitas campos del estado.

════════════════════════════════════════
FLUJO OBLIGATORIO (sigue este orden)
════════════════════════════════════════
REGLA DE ORO DE AVANCE: Antes de decidir la `action`, verifica si el usuario proporcionó el dato que faltaba en su último mensaje. Si el dato (ID o nombre) está presente en el mensaje actual, considéralo como COMPLETADO y AVANZA al siguiente paso inmediatamente. NUNCA repitas una acción de solicitud (GET_...) si el usuario acaba de dar la respuesta.

PASO 0 — CONSULTAR CITAS EXISTENTES
  - Si el usuario pregunta si tiene una cita o desea modificar/cancelar una existente → action: CHECK_APPOINTMENTS.
  - Si el sistema encuentra una cita, preséntala y pregunta si desea modificarla.
  - Si desea modificarla, solicita la nueva fecha y hora → action: MODIFY_APPOINTMENT.

PASO 1 — IDENTIFICAR AL PACIENTE
  a) Si no hay `documento` en el estado Y no lo proporcionó ahora → action: ASK_DOCUMENT
  b) Si hay `documento` pero `registered` es false → action: VALIDATE_PATIENT
  c) Si `registered` is false después de validar → ir a PASO 2
  d) Si `registered` is true → ir a PASO 3




PASO 2 — REGISTRAR PACIENTE (CONFIRMACIÓN OBLIGATORIA)
    1. Identifica qué datos faltan de: tipo_documento (CC, TI, CE, PA), nombre, apellido, documento y consentimiento.
    2. Si faltan datos, solicita la información usando un LENGUAJE NATURAL, cercano y profesional. Evita que parezca un formulario o una lista fría. 
       Asegúrate de que el usuario sepa que puede enviar todo en un solo mensaje.
       Ejemplo natural: "Lamentablemente, no encontré su registro con ese documento. Para poder ayudarle a agendar su cita, ¿podría indicarme su nombre completo, el tipo de documento que utiliza y si acepta el tratamiento de sus datos personales? Puede escribirme todo en un solo mensaje para agilizar el proceso."
    3. NUNCA solicites el teléfono; el sistema ya lo posee.
    4. Una vez que tengas TODOS los datos requeridos, NO registres inmediatamente. Primero, usa la action: CONFIRM_PATIENT_DATA.
       Resume los datos de forma natural: "Perfecto, para confirmar que todo esté correcto: su nombre es [nombre] [apellido], documento [tipo_documento] [documento] y teléfono [telefono]. ¿Es correcto?"
    5. Solo después de que el usuario confirme explícitamente que los datos son correctos → action: REGISTER_PATIENT.

PASO 3 — CONFIGURAR LA CITA
  Sigue esta secuencia lineal. Una vez completado un punto, NO vuelvas a él.
  1. ¿Falta `servicio_id`? → action: GET_SERVICES. (Si ya está, pasa al 2).
  2. ¿Falta `profesional_id`? → action: GET_PROFESSIONALS. (Si ya está, pasa al 3).
  3. ¿Falta `sede_id`? → action: GET_SEDES. (Si ya está, pasa al 4).
  4. ¿Falta `fecha`? → action: ASK_DATE (formato YYYY-MM-DD). (Si ya está, pasa al 5).
  5. ¿Tienes fecha y profesional_id O el usuario pide horarios? → action: GET_AVAILABILITY.
  6. ¿El usuario confirma la hora? → guardar en data {"hora": "HH:MM"} y avanzar al PASO 4.


PASO 4 — CREAR CITA
  Cuando tengas: documento, fecha_hora (o fecha+hora), servicio_id, profesional_id, sede_id
  → action: CREATE_APPOINTMENT

════════════════════════════════════════
VALORES PERMITIDOS
════════════════════════════════════════
intent: REGISTER_PATIENT | CREATE_APPOINTMENT | GET_SERVICES | GET_PROFESSIONALS | GET_SEDES | FAQ | UNKNOWN
action: ASK_DOC_TYPE | ASK_NAME | ASK_DOCUMENT | ASK_PHONE | ASK_SERVICE | ASK_PROFESSIONAL | ASK_DATE | ASK_TIME | GET_SERVICES | GET_PROFESSIONALS | GET_SEDES | REGISTER_PATIENT | CREATE_APPOINTMENT | VALIDATE_PATIENT | GET_AVAILABILITY | FAQ | UNKNOWN
PROMPT;
    }

    private function responseSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'intent' => ['type' => 'string', 'enum' => self::VALID_INTENTS],
                'action' => ['type' => 'string', 'enum' => self::VALID_ACTIONS],
                'message' => ['type' => 'string'],
                'data' => [
                    'type' => 'object',
                    'properties' => array_fill_keys(
                        self::VALID_DATA_KEYS,
                        ['type' => 'string', 'nullable' => true]
                    ),
                ],
            ],
            'required' => ['intent', 'action', 'message', 'data'],
        ];
    }

    private function parseAndValidate(array $result, array $context): array
    {
        $textResponse = $result['candidates'][0]['content']['parts'][0]['text'] ?? null;

        if ($textResponse === null) {
            throw new Exception('No text content in Gemini response.');
        }

        \Illuminate\Support\Facades\Log::info('[AUDIT-3] Raw text response', ['text' => $textResponse]);

        $clean = preg_replace('/^```(?:json)?\s*/i', '', trim($textResponse));
        $clean = preg_replace('/\s*```$/', '', $clean);
        $clean = preg_replace('/[^\x20-\x7E\x0A\x0D]/', '', $clean);

        $decoded = json_decode($clean, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            if (preg_match('/\{.*\}/s', $clean, $matches)) {
                $decoded = json_decode($matches[0], true);
            }
        }

        if (!$decoded || !isset($decoded['intent'], $decoded['action'], $decoded['message'])) {
            \Illuminate\Support\Facades\Log::error('[AUDIT-3] Schema mismatch', [
                'clean_text' => $clean,
                'decoded' => $decoded
            ]);
            throw new Exception('Respuesta del modelo no cumple el schema mínimo.');
        }

        $decoded['data'] = $decoded['data'] ?? [];
        $decoded['data'] = array_intersect_key($decoded['data'], array_flip(self::VALID_DATA_KEYS));

        if (!in_array($decoded['intent'], self::VALID_INTENTS, true)) {
            \Illuminate\Support\Facades\Log::warning('[AUDIT-3] intent inválido', ['intent' => $decoded['intent']]);
            $decoded['intent'] = 'UNKNOWN';
        }

        if (!in_array($decoded['action'], self::VALID_ACTIONS, true)) {
            \Illuminate\Support\Facades\Log::warning('[AUDIT-3] action inválida', ['action' => $decoded['action']]);
            $decoded['action'] = 'UNKNOWN';
        }

        return $decoded;
    }

    private function normalizeContext(array $context): array
    {
        $normalized = self::CONTEXT_DEFAULTS;
        foreach (self::CONTEXT_DEFAULTS as $key => $_) {
            if (array_key_exists($key, $context)) {
                $normalized[$key] = $context[$key];
            }
        }
        return $normalized;
    }

    private function fallbackResponse(): array
    {
        return [
            'intent' => 'UNKNOWN',
            'action' => 'UNKNOWN',
            'message' => 'Lo siento, tuve un problema procesando tu mensaje. ¿Podrías repetirlo?',
            'data' => [],
        ];
    }
}
