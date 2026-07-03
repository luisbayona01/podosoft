<?php

namespace App\Services;

use App\Models\ConversacionIA;
use App\Models\MensajeIA;

class ConversationService
{
    public function getState(string $phone, int $tenantId): array
    {
        $conv = ConversacionIA::where('telefono', $phone)
            ->where('tenant_id', $tenantId)
            ->first();

        if (!$conv) {
            return [];
        }

        $messages = MensajeIA::where('conversacion_id', $conv->id)
            ->orderBy('created_at', 'asc')
            ->limit(40)
            ->get();

        $history = $messages->map(function ($msg) {
            return [
                'role' => $msg->origen === 'paciente' ? 'user' : 'model',
                'text' => $msg->mensaje,
            ];
        })->toArray();

        return array_merge($conv->metadata ?? [], ['history' => $history]);
    }

    public function setState(string $phone, array $state, int $tenantId): void
    {
        $conv = ConversacionIA::firstOrCreate(
            ['telefono' => $phone, 'tenant_id' => $tenantId],
            ['estado' => 'activa']
        );

        // Store context/state in metadata (excluding history)
        $metadata = $state;
        unset($metadata['history']);
        
        $conv->update([
            'metadata' => $metadata,
            'ultima_interaccion' => now(),
        ]);

        // Sync history to messages table
        $history = $state['history'] ?? [];
        $existingCount = MensajeIA::where('conversacion_id', $conv->id)->count();
        
        if (count($history) > $existingCount) {
            $newMessages = array_slice($history, $existingCount);
            
            foreach ($newMessages as $msgData) {
                MensajeIA::create([
                    'conversacion_id' => $conv->id,
                    'tenant_id' => $tenantId,
                    'origen' => $msgData['role'] === 'user' ? 'paciente' : 'ia',
                    'mensaje' => $msgData['text'],
                ]);
            }
        }
    }

    public function clearState(string $phone, int $tenantId): void
    {
        $conv = ConversacionIA::where('telefono', $phone)
            ->where('tenant_id', $tenantId)
            ->first();

        if ($conv) {
            $conv->update(['estado' => 'cerrada', 'metadata' => null]);
        }
    }

    public function setDocumentPendingState(string $phone, int $tenantId, string $conversationStep = 'WAITING_DOCUMENT'): void
    {
        $this->updateConversationState($phone, $tenantId, [
            'conversation_step' => $conversationStep,
            'document_pending' => true,
        ]);
    }

    public function setPatientFoundState(string $phone, int $tenantId, int $patientId, array $patientData): void
    {
        $this->updateConversationState($phone, $tenantId, [
            'conversation_step' => 'PATIENT_FOUND',
            'document_pending' => false,
            'patient_id' => $patientId,
            'patient_found' => true,
            'documento' => $patientData['documento'] ?? null,
            'nombre' => $patientData['nombre'] ?? null,
            'apellido' => $patientData['apellido'] ?? null,
        ]);
    }

    public function setPatientNotFoundState(string $phone, int $tenantId, string $documento): void
    {
        $this->updateConversationState($phone, $tenantId, [
            'conversation_step' => 'PATIENT_NOT_FOUND',
            'document_pending' => false,
            'patient_found' => false,
            'documento' => $documento,
        ]);
    }

    public function setPatientRegisteredState(string $phone, int $tenantId, int $patientId, array $patientData): void
    {
        $this->updateConversationState($phone, $tenantId, [
            'conversation_step' => 'PATIENT_REGISTERED',
            'document_pending' => false,
            'patient_id' => $patientId,
            'patient_found' => true,
            'documento' => $patientData['documento'] ?? null,
            'nombre' => $patientData['nombre'] ?? null,
            'apellido' => $patientData['apellido'] ?? null,
            'registered_in_session' => true,
        ]);
    }

    public function setValidatingDocumentState(string $phone, int $tenantId, string $documento, string $tipoDocumento): void
    {
        $this->updateConversationState($phone, $tenantId, [
            'conversation_step' => 'VALIDATING_DOCUMENT',
            'documento' => $documento,
            'tipo_documento' => $tipoDocumento,
        ]);
    }

    public function setServicesState(string $phone, int $tenantId): void
    {
        $this->updateConversationState($phone, $tenantId, [
            'conversation_step' => 'SERVICES',
        ]);
    }

    public function setServiceSelectedState(string $phone, int $tenantId, int $servicioId): void
    {
        $this->updateConversationState($phone, $tenantId, [
            'conversation_step' => 'SERVICE_SELECTED',
            'servicio_id' => $servicioId,
        ]);
    }

    public function setWaitingProfessionalState(string $phone, int $tenantId): void
    {
        $this->updateConversationState($phone, $tenantId, [
            'conversation_step' => 'WAITING_PROFESSIONAL',
        ]);
    }

    public function setWaitingDateState(string $phone, int $tenantId): void
    {
        $this->updateConversationState($phone, $tenantId, [
            'conversation_step' => 'WAITING_DATE',
        ]);
    }

    public function setWaitingTimeState(string $phone, int $tenantId): void
    {
        $this->updateConversationState($phone, $tenantId, [
            'conversation_step' => 'WAITING_TIME',
        ]);
    }

    public function setAppointmentCreatedState(string $phone, int $tenantId, int $citaId): void
    {
        $this->updateConversationState($phone, $tenantId, [
            'conversation_step' => 'APPOINTMENT_CREATED',
            'cita_id' => $citaId,
        ]);
    }

    public function setFinishedState(string $phone, int $tenantId): void
    {
        $this->updateConversationState($phone, $tenantId, [
            'conversation_step' => 'FINISHED',
        ]);
    }

    public function setWaitingIntentState(string $phone, int $tenantId): void
    {
        $this->updateConversationState($phone, $tenantId, [
            'conversation_step' => 'WAITING_INTENT',
        ]);
    }

    private function updateConversationState(string $phone, int $tenantId, array $updates): void
    {
        $state = $this->getState($phone, $tenantId);
        $state = array_merge($state, $updates);
        $this->setState($phone, $state, $tenantId);
    }
}
