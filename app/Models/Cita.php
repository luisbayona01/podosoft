<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Cita extends Model {
    protected $fillable = ['tenant_id', 'paciente_id', 'profesional_id', 'sede_id', 'fecha_hora', 'estado', 'origen'];

    protected $casts = [
        'fecha_hora' => 'datetime',
    ];

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }

    public function paciente(): BelongsTo { return $this->belongsTo(Paciente::class); }
    public function profesional(): BelongsTo { return $this->belongsTo(Profesional::class); }
    public function sede(): BelongsTo { return $this->belongsTo(Sede::class); }
    public function servicios(): BelongsToMany { return $this->belongsToMany(Servicio::class, 'cita_servicio')->withPivot('precio_aplicado'); }
}
