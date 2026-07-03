<?php

namespace App\Http\Middleware;

use App\Services\TenantResolverService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenantAndConversation
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenantSlug = $request->route('tenant');

        if (!$tenantSlug) {
            return $next($request);
        }

        $resolver = new TenantResolverService($tenantSlug);
        $tenant = $resolver->resolve();

        if (!$tenant) {
            abort(404, 'Clínica no encontrada');
        }

        $request->attributes->set('tenant_resolver', $resolver);
        $request->attributes->set('tenant', $tenant);
        $request->attributes->set('tenant_config', $resolver->getConfig());

        view()->share('tenant_config', $resolver->getConfig());

        return $next($request);
    }

    public static function getTenant(Request $request): ?\App\Models\Tenant
    {
        return $request->attributes->get('tenant');
    }

    public static function getTenantConfig(Request $request): array
    {
        return $request->attributes->get('tenant_config', []);
    }

    public static function getTenantResolver(Request $request): ?TenantResolverService
    {
        return $request->attributes->get('tenant_resolver');
    }
}