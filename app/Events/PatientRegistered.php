<?php

namespace App\Events;

use App\Models\Paciente;
use App\Models\Tenant;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PatientRegistered
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Paciente $patient,
        public Tenant $tenant,
        public ?string $whatsappPhone = null
    ) {}
}