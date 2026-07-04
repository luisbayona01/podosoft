<?php

namespace App\Listeners;

use App\Events\PatientRegistered;
use App\Services\WhatsAppService;

class SendWelcomeWhatsAppMessage
{
    public function __construct(
        private WhatsAppService $whatsAppService
    ) {}

    public function handle(PatientRegistered $event): void
    {
        if (!$event->whatsappPhone) {
            return;
        }

        $message = "Tu registro fue exitoso.";

        try {
            $this->whatsAppService->forTenant($event->tenant->id)->sendText($event->whatsappPhone, $message);
        } catch (\Exception $e) {
            report($e);
        }
    }
}