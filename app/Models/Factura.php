<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Factura extends Model {
    protected $fillable = [
        'tenant_id', 'paciente_id', 'cita_id',
        'cliente_nombre', 'cliente_documento',
        'numero_factura', 'subtotal', 'descuento', 'impuestos', 'total',
        'fecha_emision', 'estado', 'uuid_dian'
    ];

    protected $casts = [
        'fecha_emision' => 'datetime',
        'subtotal' => 'decimal:2',
        'descuento' => 'decimal:2',
        'impuestos' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function paciente(): BelongsTo { return $this->belongsTo(Paciente::class); }
    public function cita(): BelongsTo { return $this->belongsTo(Cita::class); }
    public function items(): HasMany { return $this->hasMany(FacturaItem::class); }
    public function pagos(): HasMany { return $this->hasMany(Pago::class); }
    public function notasCredito(): HasMany { return $this->hasMany(NotaCredito::class); }

    /** Recalcula subtotal, descuento y total a partir de los ítems. */
    public function recalcularTotales(): void
    {
        $this->subtotal = $this->items->sum(fn ($i) => $i->cantidad * $i->precio_unitario);
        $this->descuento = $this->items->sum('descuento');
        $this->total = max(0, $this->subtotal - $this->descuento + $this->impuestos);
        $this->save();
    }

    /** Nombre del cliente para mostrar, con o sin paciente registrado. */
    public function getClienteDisplayAttribute(): string
    {
        if ($this->paciente) {
            return trim($this->paciente->nombre . ' ' . $this->paciente->apellido);
        }
        return $this->cliente_nombre ?: 'Cliente ocasional';
    }
}
