<?php

namespace App\Livewire;

use App\Models\BotBlockedContact;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

class BotBlockedContacts extends Component
{
    use WithPagination;

    #[Title('Números bloqueados del bot')]
    public string $phone = '';

    public string $reason = '';

    public string $errorMessage = '';

    protected function rules(): array
    {
        return [
            'phone' => 'required|string|max:30',
            'reason' => 'nullable|string|max:255',
        ];
    }

    public function mount(): void
    {
        $this->authorize('whatsapp.contacts.view');
    }

    public function add(): void
    {
        $this->authorize('whatsapp.contacts.view');
        $this->validate();

        $normalized = BotBlockedContact::normalizePhone($this->phone);

        if ($normalized === null || !preg_match('/^[0-9]{7,15}$/', $normalized)) {
            $this->addError('phone', 'El número no es válido. Incluye el código de país, ej: 573001234567.');
            return;
        }

        BotBlockedContact::updateOrCreate(
            ['phone' => $normalized],
            ['reason' => $this->reason ?: null, 'active' => true],
        );

        $this->reset(['phone', 'reason']);
        session()->flash('success', 'Número agregado a la lista de bloqueados.');
    }

    public function toggle(int $id): void
    {
        $this->authorize('whatsapp.contacts.view');

        $contact = BotBlockedContact::findOrFail($id);
        $contact->update(['active' => !$contact->active]);
    }

    public function delete(int $id): void
    {
        $this->authorize('whatsapp.contacts.view');

        BotBlockedContact::findOrFail($id)->delete();

        session()->flash('success', 'Número eliminado de la lista.');
    }

    public function render()
    {
        return view('livewire.bot-blocked-contacts', [
            'contacts' => BotBlockedContact::orderByDesc('created_at')->paginate(15),
        ]);
    }
}
