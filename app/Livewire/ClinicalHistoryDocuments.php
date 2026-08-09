<?php

namespace App\Livewire;

use App\Models\DocumentoClinico;
use App\Models\HistoriaClinica;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class ClinicalHistoryDocuments extends Component
{
    use WithFileUploads;

    public $historyId;
    public $newDocument;
    public $tipo = 'consentimiento';
    public $observations = '';
    public $documentToDelete = null;

    protected $rules = [
        'newDocument' => 'required|file|mimes:pdf,jpg,jpeg,png,webp,doc,docx|max:10240',
        'tipo' => 'required|in:consentimiento,otros',
        'observations' => 'nullable|string|max:500',
    ];

    protected $messages = [
        'newDocument.required' => 'Debe seleccionar un documento.',
        'newDocument.mimes' => 'Solo se permiten archivos: pdf, jpg, jpeg, png, webp, doc, docx.',
        'newDocument.max' => 'El archivo no puede superar los 10 MB.',
        'tipo.in' => 'Tipo de documento no válido.',
    ];

    public function mount($historyId)
    {
        $this->historyId = $historyId;
    }

    public function render()
    {
        $history = HistoriaClinica::findOrFail($this->historyId);
        return view('livewire.clinical-history-documents', [
            'documents' => $history->documentos()->orderBy('created_at', 'desc')->get(),
            'history' => $history,
        ]);
    }

    public function updatedNewDocument()
    {
        $this->validateOnly('newDocument');
    }

    public function saveDocument()
    {
        $this->validate();

        $history = HistoriaClinica::findOrFail($this->historyId);

        $file = $this->newDocument;
        $originalName = $file->getClientOriginalName();
        $mimeType = $file->getMimeType();

        $fileHash = hash_file('sha256', $file->getRealPath());
        $extension = $file->getClientOriginalExtension();
        $storedName = $fileHash . '.' . $extension;

        $existingDocument = DocumentoClinico::where('historia_clinica_id', $this->historyId)
            ->where('nombre_archivo', $originalName)
            ->first();

        if ($existingDocument) {
            $this->dispatch('notification', [
                'type' => 'warning',
                'title' => 'Documento duplicado',
                'message' => 'Este documento ya existe en la historia clínica.'
            ]);
            $this->reset(['newDocument', 'observations']);
            return;
        }

        $path = $file->storeAs('documentos-clinicos', $storedName, 'private');

        DocumentoClinico::create([
            'historia_clinica_id' => $this->historyId,
            'tipo' => $this->tipo,
            'ruta' => $path,
            'nombre_archivo' => $originalName,
            'tipo_mime' => $mimeType,
            'observaciones' => $this->observations ?: null,
        ]);

        $this->dispatch('notification', [
            'type' => 'success',
            'title' => 'Documento guardado',
            'message' => 'El documento se ha adjuntado correctamente a la historia clínica.'
        ]);

        $this->reset(['newDocument', 'tipo', 'observations']);
        $this->tipo = 'consentimiento';
    }

    public function confirmDelete($documentId)
    {
        $this->documentToDelete = DocumentoClinico::findOrFail($documentId);
        $this->dispatch('show-confirm-delete-document');
    }

    public function deleteDocument()
    {
        if (!$this->documentToDelete) {
            return;
        }

        $document = $this->documentToDelete;

        if (Storage::disk('private')->exists($document->ruta)) {
            Storage::disk('private')->delete($document->ruta);
        }

        $document->delete();

        $this->dispatch('notification', [
            'type' => 'success',
            'title' => 'Documento eliminado',
            'message' => 'El documento ha sido eliminado correctamente.'
        ]);

        $this->documentToDelete = null;
    }

    public function cancelDelete()
    {
        $this->documentToDelete = null;
    }
}