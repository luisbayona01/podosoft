<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FotografiaClinica extends Model {
    protected $table = 'fotografias_clinicas';
    protected $fillable = ['historia_clinica_id', 'ruta', 'nombre_archivo', 'tipo_mime', 'observaciones'];
    public function historiaClinica(): BelongsTo { return $this->belongsTo(HistoriaClinica::class); }
}
