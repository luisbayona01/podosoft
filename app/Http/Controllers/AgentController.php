<?php

namespace App\Http\Controllers;

use App\Http\Requests\AgentMessageRequest;
use App\Services\AgentService;
use Illuminate\Http\JsonResponse;

class AgentController extends Controller
{
    public function __invoke(AgentMessageRequest $request, AgentService $agentService): JsonResponse
    {
        \Illuminate\Support\Facades\Log::info('[AUDIT-1] Message received from WhatsApp', [
            'phone' => $request->phone,
            'message' => $request->message,
            'tenant_id' => $request->tenant_id
        ]);

        try {
            $result = $agentService->handleMessage(
                $request->phone,
                $request->message,
                $request->tenant_id
            );
            
            \Illuminate\Support\Facades\Log::info('[AUDIT-1] AgentService response received', ['result' => $result]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('[AUDIT-1] Fatal error in AgentService: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Internal server error'], 500);
        }

        return response()->json($result);
    }
}
