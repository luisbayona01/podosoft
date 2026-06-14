<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Factura extends Model {
    protected $fillable = ['tenant_id', 'cita_id', 'numero_factura', 'subtotal', 'impuestos', 'total', 'fecha_emision', 'estado', 'uuid_dian'];
    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function cita(): BelongsTo { return $this->belongsTo(Cita::class); }
    public function notasCredito(): HasMany { return $this->hasMany(NotaCredito::class); }
}
