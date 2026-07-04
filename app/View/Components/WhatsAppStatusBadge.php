<?php

namespace App\View\Components;

use App\Models\TenantWhatsAppAccount;
use Illuminate\View\Component;

class WhatsAppStatusBadge extends Component
{
    public ?TenantWhatsAppAccount $account = null;

    public function __construct()
    {
        $tenant = request()->attributes->get('tenant');

        if ($tenant) {
            $this->account = TenantWhatsAppAccount::where('tenant_id', $tenant->id)
                ->where('status', 'connected')
                ->first();
        }

        if (!$this->account) {
            $this->account = TenantWhatsAppAccount::where('tenant_id', $tenant?->id ?? auth()->user()?->tenant_id)
                ->first();
        }
    }

    public function render()
    {
        return view('components.whatsapp-status-badge');
    }
}