<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InsumoAttributeValue extends Model
{
    protected $fillable = ['insumo_id', 'attribute_id', 'valor'];

    public function insumo(): BelongsTo
    {
        return $this->belongsTo(Insumo::class);
    }

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(CategoriaInsumoAttribute::class, 'attribute_id');
    }
}
