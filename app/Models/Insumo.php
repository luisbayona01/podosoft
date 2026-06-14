<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Insumo extends Model {
    protected $fillable = ['tenant_id', 'categoria_id', 'nombre', 'codigo_sku', 'stock_actual', 'stock_minimo', 'unidad_medida'];
    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function categoria(): BelongsTo { return $this->belongsTo(CategoriaInsumo::class, 'categoria_id'); }

    public function attributeValues(): \Illuminate\Database\Eloquent\Relations\HasMany {
        return $this->hasMany(InsumoAttributeValue::class, 'insumo_id');
    }
}
