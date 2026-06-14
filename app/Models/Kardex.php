<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Kardex extends Model {
    protected $fillable = ['tenant_id', 'insumo_id', 'tipo_movimiento', 'cantidad', 'referencia', 'fecha_movimiento'];
    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function insumo(): BelongsTo { return $this->belongsTo(Insumo::class); }
}
