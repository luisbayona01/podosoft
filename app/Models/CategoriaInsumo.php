<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CategoriaInsumo extends Model
{
    protected $table = 'categorias_insumos';
    protected $fillable = ['tenant_id', 'nombre', 'descripcion'];
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
    public function insumos(): HasMany
    {
        return $this->hasMany(Insumo::class);
    }

    public function attributes(): HasMany
    {
        return $this->hasMany(CategoriaInsumoAttribute::class, 'categoria_id');
    }
}
