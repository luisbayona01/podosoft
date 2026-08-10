<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model {
    protected $fillable = ['nombre', 'slug', 'nit', 'email', 'direccion', 'logo', 'plan_id', 'estado_suscripcion', 'fecha_vencimiento', 'activo'];
    public function sedes(): HasMany { return $this->hasMany(Sede::class); }
    public function users(): HasMany { return $this->hasMany(User::class); }
    public function profesionales(): HasMany { return $this->hasMany(Profesional::class); }
    public function pacientes(): HasMany { return $this->hasMany(Paciente::class); }
    public function servicios(): HasMany { return $this->hasMany(Servicio::class); }
    public function whatsAppAccounts(): HasMany { return $this->hasMany(TenantWhatsAppAccount::class); }
    public function activeWhatsAppAccount(): HasMany { return $this->hasMany(TenantWhatsAppAccount::class)->where('status', 'connected'); }
}
