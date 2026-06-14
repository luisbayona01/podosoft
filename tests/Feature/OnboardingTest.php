<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Models\Profesional;
use App\Models\Sede;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnboardingTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_completes_onboarding_flow_successfully()
    {
        $data = [
            'clinic_name' => 'Clínica Podológica Test',
            'clinic_nit' => '123456789',
            'clinic_phone' => '1234567890',
            'clinic_email' => 'contacto@clinicatest.com',
            'clinic_city' => 'Bogotá',
            'clinic_address' => 'Calle 123 #45-67',
            'admin_name' => 'Dr. Juan Perez',
            'admin_document' => '10101010',
            'admin_email' => 'admin@clinicatest.com',
            'admin_phone' => '3001234567',
            'admin_password' => 'password123',
            'admin_password_confirmation' => 'password123',
        ];

        // We simulate the Livewire component call
        \Livewire\Livewire::test(\App\Livewire\Auth\Onboarding::class)
            ->set('clinic_name', $data['clinic_name'])
            ->set('clinic_nit', $data['clinic_nit'])
            ->set('clinic_phone', $data['clinic_phone'])
            ->set('clinic_email', $data['clinic_email'])
            ->set('clinic_city', $data['clinic_city'])
            ->set('clinic_address', $data['clinic_address'])
            ->call('nextStep')
            ->set('admin_name', $data['admin_name'])
            ->set('admin_document', $data['admin_document'])
            ->set('admin_email', $data['admin_email'])
            ->set('admin_phone', $data['admin_phone'])
            ->set('admin_password', $data['admin_password'])
            ->set('admin_password_confirmation', $data['admin_password_confirmation'])
            ->call('nextStep')
            ->call('register')
            ->assertRedirect('/dashboard');

        // Assertions
        $this->assertDatabaseHas('tenants', ['nombre' => $data['clinic_name']]);
        $this->assertDatabaseHas('profesionales', ['email' => $data['admin_email'], 'documento' => $data['admin_document']]);
        $this->assertDatabaseHas('users', ['email' => $data['admin_email'], 'name' => $data['admin_name']]);
        $this->assertDatabaseHas('sedes', ['nombre' => 'Sede Principal']);

        $user = User::where('email', $data['admin_email'])->first();
        $this->assertNotNull($user->professional);
        $this->assertEquals($user->tenant_id, $user->professional->tenant_id);
    }
}
