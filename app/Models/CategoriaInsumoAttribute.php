<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CategoriaInsumoAttribute extends Model
{
    protected $fillable = ['categoria_id', 'tenant_id', 'nombre', 'tipo'];

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(CategoriaInsumo::class, 'categoria_id');
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function values(): HasMany
    {
        return $this->hasMany(InsumoAttributeValue::class, 'attribute_id');
    }
}
