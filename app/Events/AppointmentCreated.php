<?php

namespace App\Events;

use App\Models\Cita;
use App\Models\Tenant;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AppointmentCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Cita $appointment,
        public Tenant $tenant,
        public string $whatsappPhone
    ) {}
}