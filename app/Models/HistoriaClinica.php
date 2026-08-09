<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HistoriaClinica extends Model
{
  protected $table = 'historias_clinicas';
  protected $fillable = ['tenant_id', 'paciente_id', 'cita_id', 'diagnostico', 'procedimiento', 'observaciones', 'firma_digital', 'fecha_firma', 'bloqueo_edicion'];
  public function paciente(): BelongsTo
  {
    return $this->belongsTo(Paciente::class);
  }
  public function cita(): BelongsTo
  {
    return $this->belongsTo(Cita::class);
  }
  public function fotografias(): HasMany
  {
    return $this->hasMany(FotografiaClinica::class);
  }
  public function documentos(): HasMany
  {
    return $this->hasMany(DocumentoClinico::class);
  }
  public function hallazgos(): HasMany
  {
    return $this->hasMany(ConsultaHallazgo::class);
  }
}
