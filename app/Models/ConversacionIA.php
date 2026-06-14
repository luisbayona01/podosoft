<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConversacionIA extends Model {
    protected $table = 'conversaciones_ia';
    protected $fillable = ['tenant_id', 'paciente_id', 'telefono', 'estado', 'intencion_detectada', 'confianza_modelo', 'requiere_humano', 'ultima_interaccion', 'metadata'];
    protected $casts = ['metadata' => 'array'];

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function paciente(): BelongsTo { return $this->belongsTo(Paciente::class); }
    public function mensajes(): HasMany { return $this->hasMany(MensajeIA::class); }
}
