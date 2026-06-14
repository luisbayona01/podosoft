<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pago extends Model {
    protected $fillable = [
        'tenant_id', 
        'paciente_id', 
        'cita_id', 
        'servicio_id', 
        'fecha_pago', 
        'valor', 
        'descuento', 
        'monto_final', 
        'metodo_pago', 
        'observaciones', 
        'comprobante_numero', 
        'estado'
    ];

    protected $casts = [
        'fecha_pago' => 'date',
        'valor' => 'decimal:2',
        'descuento' => 'decimal:2',
        'monto_final' => 'decimal:2',
    ];

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function cita(): BelongsTo { return $this->belongsTo(Cita::class); }
    public function paciente(): BelongsTo { return $this->belongsTo(Paciente::class); }
    public function servicio(): BelongsTo { return $this->belongsTo(Servicio::class); }
}
