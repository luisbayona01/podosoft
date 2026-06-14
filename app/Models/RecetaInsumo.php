<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecetaInsumo extends Model {
    protected $fillable = ['servicio_id', 'insumo_id', 'cantidad_consumo'];
    public function servicio(): BelongsTo { return $this->belongsTo(Servicio::class); }
    public function insumo(): BelongsTo { return $this->belongsTo(Insumo::class); }
}
