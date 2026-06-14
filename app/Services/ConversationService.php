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
}
