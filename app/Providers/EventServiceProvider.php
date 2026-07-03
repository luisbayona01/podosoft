<?php

namespace App\Providers;

use App\Events\AppointmentCreated;
use App\Events\PatientRegistered;
use App\Listeners\SendAppointmentWhatsAppNotification;
use App\Listeners\SendWelcomeWhatsAppMessage;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        PatientRegistered::class => [
            SendWelcomeWhatsAppMessage::class,
        ],
        AppointmentCreated::class => [
            SendAppointmentWhatsAppNotification::class,
        ],
    ];

    public function boot(): void
    {
        //
    }
}