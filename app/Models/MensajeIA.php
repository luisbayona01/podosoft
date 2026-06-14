<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MensajeIA extends Model {
    protected $table = 'mensajes_ia';
    protected $fillable = ['tenant_id', 'conversacion_id', 'origen', 'mensaje', 'metadata', 'token_ia', 'procesado_en'];
    protected $casts = ['metadata' => 'array'];
    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function conversacion(): BelongsTo { return $this->belongsTo(ConversacionIA::class, 'conversacion_id'); }
}
