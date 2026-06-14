<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HorarioProfesional extends Model {
    protected $fillable = ['profesional_id', 'dia_semana', 'hora_inicio', 'hora_fin', 'activo'];
    public function profesional(): BelongsTo { return $this->belongsTo(Profesional::class); }
}
