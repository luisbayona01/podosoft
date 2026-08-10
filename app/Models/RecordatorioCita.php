<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecordatorioCita extends Model {
    protected $table = 'recordatorios_citas';
    protected $fillable = ['cita_id', 'tenant_id', 'telefono', 'estado', 'sent_at'];

    public function cita(): BelongsTo
    {
        return $this->belongsTo(Cita::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}