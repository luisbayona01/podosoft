<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Profesional extends Model {
    protected $table = 'profesionales';
    protected $fillable = ['tenant_id', 'nombre', 'apellido', 'especialidad', 'numero_licencia', 'documento', 'email', 'activo'];
    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function user() { return $this->hasOne(User::class); }
    public function citas(): HasMany { return $this->hasMany(Cita::class); }
    public function horarios(): HasMany { return $this->hasMany(HorarioProfesional::class); }
}
