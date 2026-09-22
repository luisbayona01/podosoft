<?php

namespace App\Livewire;

use App\Models\GoogleCalendarConnection;
use App\Services\GoogleCalendarService;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class GoogleCalendarConfig extends Component
{
    public ?GoogleCalendarConnection $connection = null;

    public string $statusText = 'No conectado';

    public string $statusColor = 'gray';

    public bool $testing = false;

    public string $testResult = '';

    public function mount(): void
    {
        $this->loadConnection();
    }

    public function loadConnection(): void
    {
        $this->connection = GoogleCalendarConnection::forTenant(auth()->user()->tenant_id)->first();

        if ($this->connection?->isConnected()) {
            $this->statusText = 'Conectado';
            $this->statusColor = 'green';
        } elseif ($this->connection) {
            $this->statusText = 'Requiere reconexión';
            $this->statusColor = 'yellow';
        } else {
            $this->statusText = 'No conectado';
            $this->statusColor = 'gray';
        }
    }

    public function test(GoogleCalendarService $google): void
    {
        $this->testing = true;
        $this->testResult = '';

        if (!$this->connection) {
            $this->testResult = 'error:No hay conexión configurada.';
            $this->testing = false;
            return;
        }

        try {
            $result = $google->testConnection($this->connection);
            $this->testResult = 'ok:Conexión exitosa con el calendario "' . $result['calendar'] . '".';
        } catch (\Throwable $e) {
            Log::warning('[GoogleCalendar] Test failed', [
                'tenant_id' => auth()->user()->tenant_id,
                'error' => $e->getMessage(),
            ]);
            $this->testResult = 'error:' . $e->getMessage();
        }

        $this->testing = false;
    }

    public function render()
    {
        return view('livewire.google-calendar-config');
    }
}
