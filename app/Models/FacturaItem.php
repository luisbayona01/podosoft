<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FacturaItem extends Model {
    protected $fillable = [
        'factura_id', 'servicio_id', 'insumo_id',
        'descripcion', 'cantidad', 'precio_unitario', 'descuento', 'subtotal'
    ];

    protected $casts = [
        'cantidad' => 'decimal:2',
        'precio_unitario' => 'decimal:2',
        'descuento' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function factura(): BelongsTo { return $this->belongsTo(Factura::class); }
    public function servicio(): BelongsTo { return $this->belongsTo(Servicio::class); }
    public function insumo(): BelongsTo { return $this->belongsTo(Insumo::class); }
}
