<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TenantWhatsAppAccount extends Model
{
    use SoftDeletes;

    protected $table = 'tenant_whatsapp_accounts';

    protected $fillable = [
        'tenant_id',
        'provider',
        'instance_name',
        'instance_id',
        'phone',
        'status',
        'api_key',
        'server_url',
        'session',
        'connected_at',
        'last_seen_at',
        'webhook_url',
        'qr_code',
        'qr_code_base64',
        'webhook_configured',
    ];

    protected $casts = [
        'connected_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'webhook_configured' => 'boolean',
    ];

    protected $hidden = [
        'api_key',
        'session',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function isConnected(): bool
    {
        return $this->status === 'connected';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending' || $this->status === 'connecting';
    }

    public function markAsConnected(string $phone = null): void
    {
        $this->update([
            'status' => 'connected',
            'phone' => $phone ?? $this->phone,
            'connected_at' => now(),
            'last_seen_at' => now(),
        ]);
    }

    public function markAsDisconnected(): void
    {
        $this->update([
            'status' => 'disconnected',
            'last_seen_at' => now(),
        ]);
    }

    public function markAsError(string $message = null): void
    {
        $this->update([
            'status' => 'error',
        ]);
    }

    public function saveQrCode(string $qrCode, ?string $base64 = null): void
    {
        $this->update([
            'qr_code' => $qrCode,
            'qr_code_base64' => $base64,
        ]);
    }

    public function saveSession(string $sessionData): void
    {
        $this->update([
            'session' => $sessionData,
        ]);
    }

    public function saveInstanceId(string $instanceId): void
    {
        $this->update([
            'instance_id' => $instanceId,
        ]);
    }
}