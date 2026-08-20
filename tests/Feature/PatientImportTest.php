<?php

namespace Tests\Feature;

use App\Models\Paciente;
use App\Models\Tenant;
use App\Models\User;
use App\Services\PatientImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Tests\TestCase;

class PatientImportTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected User $user;

    protected Tenant $otherTenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'nombre' => 'Clínica Uno',
            'slug' => 'clinica-uno',
            'email' => 'uno@test.com',
            'telefono' => '111',
            'direccion' => 'Calle 1',
        ]);

        $this->otherTenant = Tenant::create([
            'nombre' => 'Clínica Dos',
            'slug' => 'clinica-dos',
            'email' => 'dos@test.com',
            'telefono' => '222',
            'direccion' => 'Calle 2',
        ]);

        $this->user = User::create([
            'name' => 'Usuario Test',
            'email' => 'usuario@test.com',
            'password' => bcrypt('password'),
            'tenant_id' => $this->tenant->id,
        ]);

        $this->actingAs($this->user);
    }

    protected function csv(string $body): UploadedFile
    {
        $header = 'nombre,apellido,telefono,tipo_documento,documento,email,fecha_nacimiento,sexo,direccion';

        return UploadedFile::fake()->createWithContent(
            'pacientes.csv',
            $header . "\n" . $body
        );
    }

    /** @test */
    public function imports_valid_csv_creating_patients()
    {
        $csv = $this->csv("Carlos,Perez,3001234567,,,,,,\n");

        Livewire::test(\App\Livewire\PatientImport::class)
            ->set('file', $csv)
            ->call('parseFile')
            ->assertSet('step', 'preview')
            ->call('confirmImport')
            ->assertSet('step', 'result')
            ->assertSet('summary.imported', 1)
            ->assertSet('summary.found', 1);

        $this->assertDatabaseHas('pacientes', [
            'tenant_id' => $this->tenant->id,
            'nombre' => 'Carlos',
            'apellido' => 'Perez',
            'telefono' => '3001234567',
        ]);
    }

    /** @test */
    public function imports_valid_csv_using_semicolon_delimiter()
    {
        $header = 'nombre;apellido;telefono;tipo_documento;documento;email;fecha_nacimiento;sexo;direccion';
        $csv = UploadedFile::fake()->createWithContent(
            'pacientes.csv',
            $header . "\n" . "Maria;Gomez;3009876543;;;;;;\n"
        );

        Livewire::test(\App\Livewire\PatientImport::class)
            ->set('file', $csv)
            ->call('parseFile')
            ->assertSet('step', 'preview')
            ->call('confirmImport')
            ->assertSet('step', 'result')
            ->assertSet('summary.imported', 1)
            ->assertSet('summary.found', 1);

        $this->assertDatabaseHas('pacientes', [
            'tenant_id' => $this->tenant->id,
            'nombre' => 'Maria',
            'apellido' => 'Gomez',
            'telefono' => '3009876543',
        ]);
    }

    /** @test */
    public function marks_row_as_error_when_nombre_is_empty()
    {
        $csv = $this->csv(",Perez,3001234567,,,,,,\n");

        Livewire::test(\App\Livewire\PatientImport::class)
            ->set('file', $csv)
            ->call('parseFile')
            ->call('confirmImport')
            ->assertSet('step', 'result')
            ->assertSet('summary.imported', 0)
            ->assertSet('summary.errors_rows', 1);

        $this->assertDatabaseMissing('pacientes', ['apellido' => 'Perez']);
    }

    /** @test */
    public function marks_row_as_error_when_apellido_is_empty()
    {
        $csv = $this->csv("Carlos,,3001234567,,,,,,\n");

        Livewire::test(\App\Livewire\PatientImport::class)
            ->set('file', $csv)
            ->call('parseFile')
            ->call('confirmImport')
            ->assertSet('summary.imported', 0)
            ->assertSet('summary.errors_rows', 1);

        $this->assertDatabaseMissing('pacientes', ['nombre' => 'Carlos']);
    }

    /** @test */
    public function marks_row_as_error_when_telefono_is_empty()
    {
        $csv = $this->csv("Carlos,Perez,,,,,,,\n");

        Livewire::test(\App\Livewire\PatientImport::class)
            ->set('file', $csv)
            ->call('parseFile')
            ->call('confirmImport')
            ->assertSet('summary.imported', 0)
            ->assertSet('summary.errors_rows', 1);

        $this->assertDatabaseMissing('pacientes', ['nombre' => 'Carlos']);
    }

    /** @test */
    public function accepts_rows_without_documento()
    {
        $csv = $this->csv("Maria,Gomez,3019876543,,,,,,\n");

        Livewire::test(\App\Livewire\PatientImport::class)
            ->set('file', $csv)
            ->call('parseFile')
            ->call('confirmImport')
            ->assertSet('summary.imported', 1)
            ->assertSet('summary.errors_rows', 0);

        $paciente = Paciente::where('telefono', '3019876543')->first();

        $this->assertNotNull($paciente);
        $this->assertNull($paciente->documento);
    }

    /** @test */
    public function accepts_rows_without_tipo_documento()
    {
        $csv = $this->csv("Maria,Gomez,3019876543,,10203040,,\n");

        Livewire::test(\App\Livewire\PatientImport::class)
            ->set('file', $csv)
            ->call('parseFile')
            ->call('confirmImport')
            ->assertSet('summary.imported', 1);

        $paciente = Paciente::where('telefono', '3019876543')->first();

        $this->assertNotNull($paciente);
        $this->assertNull($paciente->tipo_documento);
        $this->assertEquals('10203040', $paciente->documento);
    }

    /** @test */
    public function accepts_rows_with_empty_email()
    {
        $csv = $this->csv("Carlos,Perez,3001234567,CC,1,,2000-01-01,Masculino,\n");

        Livewire::test(\App\Livewire\PatientImport::class)
            ->set('file', $csv)
            ->call('parseFile')
            ->call('confirmImport')
            ->assertSet('summary.imported', 1);

        $paciente = Paciente::where('telefono', '3001234567')->first();

        $this->assertNotNull($paciente);
        $this->assertNull($paciente->email);
    }

    /** @test */
    public function marks_duplicate_when_same_phone_already_exists()
    {
        Paciente::create([
            'tenant_id' => $this->tenant->id,
            'nombre' => 'Existente',
            'apellido' => 'Ya',
            'telefono' => '3001234567',
        ]);

        $csv = $this->csv("Carlos,Perez,3001234567,,,,,,\n");

        Livewire::test(\App\Livewire\PatientImport::class)
            ->set('file', $csv)
            ->call('parseFile')
            ->assertSet('counts.duplicates', 1)
            ->call('confirmImport')
            ->assertSet('step', 'result')
            ->assertSet('summary.imported', 0)
            ->assertSet('summary.duplicates', 1);

        $this->assertEquals(1, Paciente::where('tenant_id', $this->tenant->id)->count());
    }

    /** @test */
    public function marks_duplicate_when_same_document_already_exists()
    {
        Paciente::create([
            'tenant_id' => $this->tenant->id,
            'tipo_documento' => 'CC',
            'documento' => '10203040',
            'nombre' => 'Existente',
            'apellido' => 'Ya',
            'telefono' => '2999999999',
        ]);

        $csv = $this->csv("Carlos,Perez,3001234567,CC,10203040,,,,,,\n");

        Livewire::test(\App\Livewire\PatientImport::class)
            ->set('file', $csv)
            ->call('parseFile')
            ->assertSet('counts.duplicates', 1)
            ->call('confirmImport')
            ->assertSet('summary.imported', 0)
            ->assertSet('summary.duplicates', 1);

        $this->assertEquals(1, Paciente::where('tenant_id', $this->tenant->id)->count());
    }

    /** @test */
    public function imports_to_authenticated_tenant_only()
    {
        $csv = $this->csv("Carlos,Perez,3001234567,,,,,,\n");

        Livewire::test(\App\Livewire\PatientImport::class)
            ->set('file', $csv)
            ->call('parseFile')
            ->call('confirmImport')
            ->assertSet('summary.imported', 1);

        $this->assertEquals(1, Paciente::where('tenant_id', $this->tenant->id)->count());
        $this->assertEquals(0, Paciente::where('tenant_id', $this->otherTenant->id)->count());
    }

    /** @test */
    public function ignores_tenant_id_column_from_csv()
    {
        $header = 'nombre,apellido,telefono,tipo_documento,documento,email,fecha_nacimiento,sexo,direccion,tenant_id';
        $csv = UploadedFile::fake()->createWithContent(
            'pacientes.csv',
            $header . "\nCarlos,Perez,3001234567,,,,,,,{$this->otherTenant->id}\n"
        );

        Livewire::test(\App\Livewire\PatientImport::class)
            ->set('file', $csv)
            ->call('parseFile')
            ->call('confirmImport')
            ->assertSet('summary.imported', 1);

        $paciente = Paciente::where('telefono', '3001234567')->first();

        $this->assertNotNull($paciente);
        $this->assertEquals($this->tenant->id, $paciente->tenant_id);
    }

    /** @test */
    public function stores_optional_fields_as_null()
    {
        Paciente::create([
            'tenant_id' => $this->tenant->id,
            'nombre' => 'Previo',
            'apellido' => 'A',
            'telefono' => '3000000000',
        ]);

        $csv = $this->csv("Juan,Rodriguez,3205551234,,,,,,\n");

        Livewire::test(\App\Livewire\PatientImport::class)
            ->set('file', $csv)
            ->call('parseFile')
            ->call('confirmImport')
            ->assertSet('summary.imported', 1);

        $paciente = Paciente::where('telefono', '3205551234')->first();

        $this->assertNull($paciente->tipo_documento);
        $this->assertNull($paciente->documento);
        $this->assertNull($paciente->email);
        $this->assertNull($paciente->fecha_nacimiento);
        $this->assertNull($paciente->sexo);
        $this->assertNull($paciente->direccion);
    }

    /** @test */
    public function normalizes_phone_numbers()
    {
        $csv = $this->csv("Carlos,Perez,+57 300-123-4567,,,,,,\n");

        Livewire::test(\App\Livewire\PatientImport::class)
            ->set('file', $csv)
            ->call('parseFile')
            ->call('confirmImport')
            ->assertSet('summary.imported', 1);

        $this->assertDatabaseHas('pacientes', ['telefono' => '+573001234567']);
    }

    /** @test */
    public function downloads_csv_template()
    {
        Livewire::test(\App\Livewire\PatientImport::class)
            ->call('downloadTemplate')
            ->assertFileDownloaded('plantilla-importar-pacientes.csv');
    }

    /** @test */
    public function downloads_errors_csv()
    {
        $csv = $this->csv(",Garcia,3001234567,,,,,,\nCarlos,Perez,3001234568,,,,,,\n");

        Livewire::test(\App\Livewire\PatientImport::class)
            ->set('file', $csv)
            ->call('parseFile')
            ->call('confirmImport')
            ->assertSet('summary.imported', 1)
            ->assertSet('summary.errors_rows', 1)
            ->call('downloadErrors')
            ->assertFileDownloaded('errores-importacion-pacientes.csv');
    }

    /** @test */
    public function import_incomplete_flows_reusable_for_future_pipeline()
    {
        $csv = $this->csv("Carlos,Perez,+57 300-123-4567,CC,10203040,carlos@test.com,10/05/1990,Masculino,Calle 123\n");

        Livewire::test(\App\Livewire\PatientImport::class)
            ->set('file', $csv)
            ->call('parseFile')
            ->call('confirmImport')
            ->assertSet('summary.imported', 1);

        $this->assertDatabaseHas('pacientes', [
            'tenant_id' => $this->tenant->id,
            'tipo_documento' => 'CC',
            'documento' => '10203040',
            'telefono' => '+573001234567',
            'email' => 'carlos@test.com',
            'fecha_nacimiento' => '1990-05-10',
            'sexo' => 'Masculino',
            'direccion' => 'Calle 123',
        ]);
    }

    /** @test */
    public function service_rejects_invalid_dates_and_emails_as_row_errors()
    {
        $csv = $this->csv("Carlos,Perez,3001234567,,,correo-invalido,31/13/2020,,\n");

        $result = app(PatientImportService::class)->analyze($csv->get(), $this->tenant->id);

        $this->assertEquals(1, $result->errorCount());
    }
}