<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsultaHallazgo extends Model {
    protected $fillable = ['historia_clinica_id', 'pie_zona_id', 'observaciones', 'severidad', 'metadatos_graficos'];
    protected $casts = ['metadatos_graficos' => 'array'];
    public function historiaClinica(): BelongsTo { return $this->belongsTo(HistoriaClinica::class); }
    public function zona(): BelongsTo { return $this->belongsTo(PieZona::class, 'pie_zona_id'); }
}
