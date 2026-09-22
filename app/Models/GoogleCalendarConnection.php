<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoogleCalendarConnection extends Model
{
    protected $fillable = [
        'tenant_id',
        'google_email',
        'google_account_id',
        'calendar_id',
        'access_token',
        'refresh_token',
        'token_expires_at',
        'scopes',
        'connected_at',
    ];

    protected function casts(): array
    {
        return [
            // Tokens are stored encrypted at rest (Laravel encrypted cast)
            'access_token' => 'encrypted',
            'refresh_token' => 'encrypted',
            'token_expires_at' => 'datetime',
            'connected_at' => 'datetime',
        ];
    }

    /**
     * Never leak tokens into arrays/JSON responses/logs.
     */
    protected $hidden = [
        'access_token',
        'refresh_token',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function scopeForTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function isConnected(): bool
    {
        return $this->connected_at !== null && $this->refresh_token !== null;
    }
}
