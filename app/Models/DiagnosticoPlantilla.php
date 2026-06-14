<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiagnosticoPlantilla extends Model
{
    use HasFactory;

    protected $table = 'diagnostico_plantillas';

    protected $fillable = [
        'tenant_id',
        'nombre',
        'diagnostico_base',
        'procedimiento_base',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
