<?php

namespace App\Listeners;

use App\Events\AppointmentCreated;
use App\Jobs\NotifyAppointmentCreated;

class SendAppointmentWhatsAppNotification
{
    public function handle(AppointmentCreated $event): void
    {
        NotifyAppointmentCreated::dispatch($event);
    }
}