<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Valida el header X-Service-Token para los endpoints internos consumidos
 * por el microservicio Python (podosoft-ai).
 *
 * Si no hay token configurado en el servidor, el middleware no bloquea
 * (compatibilidad con despliegues existentes).
 */
class VerifyServiceToken
{
    public function handle(Request $request, Closure $next)
    {
        $token = config('services.laravel.service_token');

        if (!empty($token)) {
            $provided = $request->header('X-Service-Token');
            if (!is_string($provided) || !hash_equals($token, $provided)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Service token inválido o ausente',
                ], 401);
            }
        }

        return $next($request);
    }
}
