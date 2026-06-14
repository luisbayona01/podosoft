<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PacienteAlergia extends Model {
    protected $fillable = ['paciente_id', 'descripcion', 'severidad'];
    public function paciente(): BelongsTo { return $this->belongsTo(Paciente::class); }
}
