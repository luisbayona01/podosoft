<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BloqueoAgenda extends Model
{
    protected $table = 'bloqueos_agenda';

    protected $fillable = ['profesional_id', 'sede_id', 'fecha_inicio', 'fecha_fin', 'motivo', 'tipo'];
    public function profesional(): BelongsTo { return $this->belongsTo(Profesional::class); }
    public function sede(): BelongsTo { return $this->belongsTo(Sede::class); }
}
