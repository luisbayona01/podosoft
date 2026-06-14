<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sede extends Model {
    protected $fillable = ['tenant_id', 'nombre', 'direccion', 'telefono', 'email', 'activo'];
    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function citas(): HasMany { return $this->hasMany(Cita::class); }
}
