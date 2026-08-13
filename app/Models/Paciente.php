<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Paciente extends Model 
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'tenant_id',
        'tipo_documento',
        'documento',
        'nombre',
        'apellido',
        'fecha_nacimiento',
        'sexo',
        'telefono',
        'email',
        'direccion',
        'consentimiento',
        'fecha_consentimiento',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'fecha_consentimiento' => 'datetime',
        'consentimiento' => 'boolean',
    ];

    public function tenant(): BelongsTo 
    { 
        return $this->belongsTo(Tenant::class); 
    }

    public function citas(): HasMany 
    { 
        return $this->hasMany(Cita::class); 
    }

    public function historiaClinica(): HasMany 
    { 
        return $this->hasMany(HistoriaClinica::class); 
    }

    public function antecedents(): BelongsToMany
    {
        return $this->belongsToMany(AntecedentType::class, 'pacientes_antecedentes_rel', 'paciente_id', 'antecedente_tipo_id')
                    ->withPivot('notes')
                    ->withTimestamps();
    }

    public function hasRiskAntecedents(): bool
    {
        return $this->antecedents()->where('is_risk', true)->exists();
    }

    public function missingImportantFields(): array
    {
        $missing = [];

        if (empty($this->tipo_documento) || empty($this->documento)) {
            $missing[] = 'documento';
        }

        if (empty($this->telefono)) {
            $missing[] = 'teléfono';
        }

        if (empty($this->email)) {
            $missing[] = 'correo electrónico';
        }

        if (empty($this->fecha_nacimiento)) {
            $missing[] = 'fecha de nacimiento';
        }

        return $missing;
    }

    public function isInformationIncomplete(): bool
    {
        return count($this->missingImportantFields()) > 0;
    }
}
