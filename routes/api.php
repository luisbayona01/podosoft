<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\PatientController;
use App\Http\Controllers\Api\TenantController;
use App\Http\Controllers\Api\InternalSignUrlController;
use App\Http\Controllers\Api\InternalPatientsByPhoneController;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\WhatsAppWebhookController;

//http://192.168.2.9:8000/api/agent/message
// API Endpoints for AI Agent (n8n)


Route::post('/agent/message', AgentController::class);

Route::post('/webhooks/evolution', [WhatsAppWebhookController::class, 'handleEvolution']);

Route::post('/webhook/whatsapp/{instance}', [WhatsAppWebhookController::class, 'handle'])
    ->name('api.webhook.whatsapp');

Route::prefix('v1')->middleware('service.token')->group(function () {
    // Identifica la compañía mediante un slug y retorna su tenant_id
    // Params: slug (required, string)
    Route::post('/tenant/resolve', [TenantController::class, 'resolve'])->name('api.tenant.resolve');

    // Registra la ficha del paciente antes de agendar la cita
    // Params: tenant_id (required, int), tipo_documento (required, string), documento (required, string), nombre (required, string), apellido (required, string), telefono (required, string), email (nullable, email), fecha_nacimiento (nullable, date), sexo (nullable, string), direccion (nullable, string), consentimiento (required, boolean)
    Route::post('/patients/register', [PatientController::class, 'register'])->name('api.patients.register');

    // Busca un paciente por documento dentro de un tenant
    // Params: documento (required, string), tenant_id (required, int)
    Route::get('/patients/by-document', [PatientController::class, 'byDocument'])->name('api.patients.by-document');

    // Registra la cita final validando disponibilidad de fecha y hora
    // Params: tenant_id (required, int), documento (required, string), fecha_hora (required, Y-m-d H:i), servicio_id (required, int), profesional_id (required, int), sede_id (required, int)
    Route::post('/appointments', [AppointmentController::class, 'store'])->name('api.appointments.store');

    // Consulta los horarios disponibles de un profesional en una fecha
    // Params: profesional_id (required, int), fecha (required, Y-m-d), servicio_id (optional, int)
    Route::get('/appointments/availability', [AppointmentController::class, 'getAvailability'])->name('api.appointments.availability');

    // Consulta las próximas citas de un paciente
    // Params: documento (required, string), tenant_id (required, int)
    Route::get('/appointments/patient', [AppointmentController::class, 'getPatientAppointments'])->name('api.appointments.patient');

    // Reprograma una cita existente
    // Params: fecha_hora (required, Y-m-d H:i), tenant_id (optional, int)
    Route::put('/appointments/{id}', [AppointmentController::class, 'update'])->name('api.appointments.update');

    // Cancela una cita existente
    // Body: tenant_id (optional, int), motivo (optional, string)
    Route::delete('/appointments/{id}', [AppointmentController::class, 'cancel'])->name('api.appointments.cancel');

    // Consulta la lista de servicios disponibles para ofrecer al paciente
    // Params: None
    Route::get('/appointments/services', [AppointmentController::class, 'getServices'])->name('api.appointments.services');

    // Consulta los profesionales disponibles y sus especialidades
    // Params: None
    Route::get('/appointments/professionals', [AppointmentController::class, 'getProfessionals'])->name('api.appointments.professionals');

    // Consulta las sedes físicas disponibles y sus direcciones
    // Params: None
    Route::get('/appointments/sedes', [AppointmentController::class, 'getSedes'])->name('api.appointments.sedes');

    // Internal endpoints (consumed by the Python AI service)
    Route::post('/internal/sign-url', InternalSignUrlController::class)->name('api.internal.sign-url');
    Route::get('/internal/patients/by-phone', InternalPatientsByPhoneController::class)->name('api.internal.patients.by-phone');
});
