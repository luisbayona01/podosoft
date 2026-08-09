<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentoClinico extends Model {
    protected $table = 'documentos_clinicos';
    protected $fillable = ['historia_clinica_id', 'tipo', 'ruta', 'nombre_archivo', 'tipo_mime', 'observaciones'];

    public function historiaClinica(): BelongsTo
    {
        return $this->belongsTo(HistoriaClinica::class);
    }
}