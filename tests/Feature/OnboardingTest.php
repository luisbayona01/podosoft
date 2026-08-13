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
            'clinic_email' => 'contacto@clinicatest.com',
            'clinic_city' => 'Bogotá',
            'clinic_address' => 'Calle 123 #45-67',
            'admin_name' => 'Dr. Juan Perez',
            'admin_apellido' => 'Admin',
            'admin_document' => '10101010',
            'admin_licencia' => 'LIC123',
            'admin_email' => 'admin@clinicatest.com',
            'admin_password' => 'password123',
            'admin_password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/register/submit', $data);

        $response->assertStatus(200);
        $response->assertJson(['redirect' => '/dashboard']);

        $this->assertDatabaseHas('tenants', ['nombre' => $data['clinic_name']]);
        $this->assertDatabaseHas('profesionales', ['email' => $data['admin_email'], 'documento' => $data['admin_document']]);
        $this->assertDatabaseHas('users', ['email' => $data['admin_email'], 'name' => $data['admin_name']]);
        $this->assertDatabaseHas('sedes', ['nombre' => 'Sede Principal']);

        $user = User::where('email', $data['admin_email'])->first();
        $this->assertNotNull($user->professional);
        $this->assertEquals($user->tenant_id, $user->professional->tenant_id);
    }

    /** @test */
    public function it_validates_required_fields_on_registration()
    {
        $response = $this->postJson('/register/submit', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'clinic_name', 'clinic_nit', 'clinic_email',
            'clinic_city', 'clinic_address', 'admin_name', 'admin_apellido',
            'admin_document', 'admin_licencia', 'admin_email',
            'admin_password'
        ]);
    }

    /** @test */
    public function it_validates_duplicate_email()
    {
        User::create([
            'name' => 'Existing User',
            'email' => 'admin@clinicatest.com',
            'password' => bcrypt('password'),
        ]);

        $data = [
            'clinic_name' => 'Clínica Podológica Test',
            'clinic_nit' => '123456789',
            'clinic_email' => 'contacto@clinicatest.com',
            'clinic_city' => 'Bogotá',
            'clinic_address' => 'Calle 123 #45-67',
            'admin_name' => 'Dr. Juan Perez',
            'admin_apellido' => 'Admin',
            'admin_document' => '10101010',
            'admin_licencia' => 'LIC123',
            'admin_email' => 'admin@clinicatest.com',
            'admin_password' => 'password123',
            'admin_password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/register/submit', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['admin_email']);
    }
}