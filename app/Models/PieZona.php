<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PieZona extends Model {
    protected $fillable = ['tenant_id', 'nombre', 'codigo_zona', 'coordenadas_svg'];
    protected $casts = ['coordenadas_svg' => 'array'];
    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
}
