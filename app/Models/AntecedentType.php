<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AntecedentType extends Model
{
    use HasFactory;

    protected $table = 'antecedentes_tipos';

    protected $fillable = ['name', 'is_risk'];

    protected $casts = [
        'is_risk' => 'boolean',
    ];

    public function patients(): BelongsToMany
    {
        return $this->belongsToMany(Patient::class, 'pacientes_antecedentes_rel', 'antecedente_tipo_id', 'paciente_id')
                    ->withPivot('notes')
                    ->withTimestamps();
    }
}
