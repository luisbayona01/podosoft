<?php

namespace App\Livewire;

use App\Models\FotografiaClinica;
use App\Models\HistoriaClinica;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class ClinicalHistoryPhotos extends Component
{
    use WithFileUploads;

    public $historyId;
    public $newPhoto;
    public $observations = '';
    public $showModal = false;
    public $photoToDelete = null;
    public $selectedPhoto = null;

    protected $rules = [
        'newPhoto' => 'required|file|mimes:jpg,jpeg,png,webp|max:5120',
        'observations' => 'nullable|string|max:500',
    ];

    protected $messages = [
        'newPhoto.required' => 'Debe seleccionar una imagen.',
        'newPhoto.mimes' => 'Solo se permiten archivos: jpg, jpeg, png, webp.',
        'newPhoto.max' => 'El archivo no puede superar los 5 MB.',
    ];

    public function mount($historyId)
    {
        $this->historyId = $historyId;
    }

    public function render()
    {
        $history = HistoriaClinica::findOrFail($this->historyId);
        return view('livewire.clinical-history-photos', [
            'photos' => $history->fotografias()->orderBy('created_at', 'desc')->get(),
            'history' => $history,
        ]);
    }

    public function updatedNewPhoto()
    {
        $this->validateOnly('newPhoto');
    }

    public function savePhoto()
    {
        $this->validate();

        $history = HistoriaClinica::findOrFail($this->historyId);

        $file = $this->newPhoto;
        $originalName = $file->getClientOriginalName();
        $mimeType = $file->getMimeType();

        $fileHash = hash_file('sha256', $file->getRealPath());
        $extension = $file->getClientOriginalExtension();
        $storedName = $fileHash . '.' . $extension;

        $existingPhoto = FotografiaClinica::where('historia_clinica_id', $this->historyId)
            ->where('nombre_archivo', $originalName)
            ->first();

        if ($existingPhoto) {
            $this->dispatch('notification', [
                'type' => 'warning',
                'title' => 'Archivo duplicado',
                'message' => 'Esta fotografía ya existe en el registro.'
            ]);
            $this->reset(['newPhoto', 'observations']);
            return;
        }

        $path = $file->storeAs('fotografias-clinicas', $storedName, 'private');

        FotografiaClinica::create([
            'historia_clinica_id' => $this->historyId,
            'ruta' => $path,
            'nombre_archivo' => $originalName,
            'tipo_mime' => $mimeType,
            'observaciones' => $this->observations ?: null,
        ]);

        $this->dispatch('notification', [
            'type' => 'success',
            'title' => 'Fotografía guardada',
            'message' => 'La fotografía se ha subido correctamente.'
        ]);

        $this->reset(['newPhoto', 'observations']);
    }

    public function confirmDelete($photoId)
    {
        $this->photoToDelete = FotografiaClinica::findOrFail($photoId);
        $this->dispatch('show-confirm-delete');
    }

    public function deletePhoto()
    {
        if (!$this->photoToDelete) {
            return;
        }

        $photo = $this->photoToDelete;

        if (Storage::disk('private')->exists($photo->ruta)) {
            Storage::disk('private')->delete($photo->ruta);
        }

        $photo->delete();

        $this->dispatch('notification', [
            'type' => 'success',
            'title' => 'Fotografía eliminada',
            'message' => 'La fotografía ha sido eliminada correctamente.'
        ]);

        $this->photoToDelete = null;
    }

    public function cancelDelete()
    {
        $this->photoToDelete = null;
    }

    public function viewPhoto($photoId)
    {
        $this->selectedPhoto = FotografiaClinica::findOrFail($photoId);
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->selectedPhoto = null;
    }

    public function getPhotoUrl($photo)
    {
        if (Storage::disk('private')->exists($photo->ruta)) {
            return route('clinical-history.photo', ['photo' => $photo->id]);
        }
        return null;
    }

    public function getThumbnailUrl($photo)
    {
        return $this->getPhotoUrl($photo);
    }
}