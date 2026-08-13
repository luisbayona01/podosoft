<?php

namespace App\Livewire;

use App\Services\WhatsAppContactsService;
use Livewire\Attributes\Title;
use Livewire\Component;

class WhatsAppContacts extends Component
{
    #[Title('Contactos de WhatsApp')]
    public array $account = [];

    public array $contacts = [];

    public array $summary = [
        'found' => 0,
        'personal' => 0,
        'groups' => 0,
        'invalid' => 0,
        'others' => 0,
    ];

    public int $pagesProcessed = 0;

    public int $duplicatesFound = 0;

    public string $search = '';

    public string $filter = 'all';

    public int $page = 1;

    public int $perPage = 25;

    public bool $hasFetched = false;

    public bool $isLoading = false;

    public string $errorMessage = '';

    public string $successMessage = '';

    protected function rules(): array
    {
        return [];
    }

    public function mount(): void
    {
        $this->authorize('whatsapp.contacts.view');

        $service = app(WhatsAppContactsService::class);
        $account = $service->accountFor($this->tenantId());

        if ($account) {
            $this->account = $service->accountSafeFields($account);
        } else {
            $this->errorMessage = 'No se encontró una cuenta de WhatsApp configurada para esta clínica.';
        }
    }

    public function fetchContacts(bool $forceRefresh = false): void
    {
        $this->authorize('whatsapp.contacts.view');

        $this->isLoading = true;
        $this->errorMessage = '';
        $this->successMessage = '';
        $this->page = 1;

        try {
            $result = app(WhatsAppContactsService::class)->fetchFor($this->tenantId(), $forceRefresh);

            if (!$result['ok']) {
                $this->errorMessage = $result['error'] ?? 'No fue posible conectarse con WhatsApp.';
                $this->contacts = [];

                return;
            }

            $this->account = $result['account'];
            $this->contacts = $result['contacts'];
            $this->summary = $result['summary'];
            $this->pagesProcessed = $result['pages'];
            $this->duplicatesFound = $result['duplicates'];
            $this->hasFetched = true;

            $this->successMessage = $result['from_cache']
                ? 'Contactos cargados desde la última consulta.'
                : 'Contactos obtenidos correctamente.';
        } catch (\Throwable $e) {
            \Log::error('[WhatsAppContacts] Error al obtener contactos', ['error' => $e->getMessage()]);
            $this->errorMessage = 'No fue posible conectarse con WhatsApp.';
        } finally {
            $this->isLoading = false;
        }
    }

    public function refreshContacts(): void
    {
        $this->fetchContacts(true);
    }

    public function downloadCsv()
    {
        $this->authorize('whatsapp.contacts.export');

        $contacts = $this->filteredContacts();

        if (empty($contacts)) {
            session()->flash('error', 'No hay contactos para descargar.');
            return null;
        }

        $content = app(WhatsAppContactsService::class)->buildGeneralCsv($contacts);

        return response()->streamDownload(function () use ($content) {
            echo $content;
        }, 'contactos-whatsapp.csv', ['Content-Type' => 'text/csv']);
    }

    public function downloadPatientsCsv()
    {
        $this->authorize('whatsapp.contacts.export');

        $personalContacts = array_values(array_filter(
            $this->contacts,
            fn (array $contact) => ($contact['class'] ?? null) === 'personal'
        ));

        if (empty($personalContacts)) {
            session()->flash('error', 'No hay contactos personales para descargar.');
            return null;
        }

        $content = app(WhatsAppContactsService::class)->buildPatientsCsv($personalContacts);

        return response()->streamDownload(function () use ($content) {
            echo $content;
        }, 'contactos-para-pacientes.csv', ['Content-Type' => 'text/csv']);
    }

    public function updatedSearch(): void
    {
        $this->page = 1;
    }

    public function updatedFilter(): void
    {
        $this->page = 1;
    }

    public function goToPage(int $page): void
    {
        $this->page = max(1, $page);
    }

    protected function filteredContacts(): array
    {
        $contacts = $this->contacts;

        if ($this->filter !== 'all') {
            $contacts = array_values(array_filter($contacts, function (array $contact) {
                return match ($this->filter) {
                    'contactos' => ($contact['class'] ?? null) === 'personal',
                    'grupos' => ($contact['class'] ?? null) === 'group',
                    'invalidos' => in_array($contact['class'] ?? null, ['invalid', 'broadcast', 'newsletter', 'device'], true),
                    default => true,
                };
            }));
        }

        if (trim($this->search) !== '') {
            $needle = mb_strtolower(trim($this->search));

            $contacts = array_values(array_filter($contacts, function (array $contact) use ($needle) {
                return mb_strpos(mb_strtolower($contact['name'] ?? ''), $needle) !== false
                    || mb_strpos(mb_strtolower($contact['push_name'] ?? ''), $needle) !== false
                    || mb_strpos((string) ($contact['phone'] ?? ''), $needle) !== false;
            }));
        }

        return $contacts;
    }

    protected function tenantId(): int
    {
        return (int) (auth()->user()->tenant_id ?? 1);
    }

    public function render()
    {
        $filtered = $this->filteredContacts();

        $totalFiltered = count($filtered);

        $maxPage = max(1, (int) ceil($totalFiltered / $this->perPage));

        if ($this->page > $maxPage) {
            $this->page = $maxPage;
        }

        $offset = ($this->page - 1) * $this->perPage;

        $visibleContacts = array_slice($filtered, $offset, $this->perPage);

        return view('livewire.whatsapp-contacts', [
            'visibleContacts' => $visibleContacts,
            'totalFiltered' => $totalFiltered,
            'maxPage' => $maxPage,
        ]);
    }
}