<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotaCredito extends Model {
    protected $fillable = ['tenant_id', 'factura_id', 'numero_nota', 'valor', 'motivo', 'uuid_dian'];
    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function factura(): BelongsTo { return $this->belongsTo(Factura::class); }
}
