<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class DebugSignatureMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $fullUrl = $request->fullUrl();
        $parsedUrl = parse_url($fullUrl);
        parse_str($parsedUrl['query'] ?? '', $queryParams);

        $hasValidSignature = $request->hasValidSignature();

        $signatureBase64 = $queryParams['signature'] ?? null;
        $expiresAt = $queryParams['expires'] ?? null;

        Log::channel('single')->info('[DEBUG-SIGNATURE] REQUEST RECIBIDO', [
            'timestamp' => now()->toIso8601String(),
            'timestamp_unix' => now()->timestamp,
            'full_url' => $fullUrl,
            'url' => $request->url(),
            'scheme' => $request->getScheme(),
            'host' => $request->getHost(),
            'path' => $request->path(),
            'is_secure' => $request->isSecure(),
            'query_string' => $request->getQueryString(),
            'query_params' => $queryParams,
            'signature' => $signatureBase64,
            'signature_length' => $signatureBase64 ? strlen($signatureBase64) : null,
            'expires' => $expiresAt,
            'expires_datetime' => $expiresAt ? date('Y-m-d H:i:s', (int)$expiresAt) : null,
            'expires_is_past' => $expiresAt ? ((int)$expiresAt < now()->timestamp) : null,
            'has_valid_signature' => $hasValidSignature,
            'app_url' => config('app.url'),
            'app_url_scheme' => parse_url(config('app.url'), PHP_URL_SCHEME),
            'x_forwarded_proto' => $request->header('X-Forwarded-Proto'),
            'x_forwarded_host' => $request->header('X-Forwarded-Host'),
            'x_forwarded_port' => $request->header('X-Forwarded-Port'),
            'x_forwarded_for' => $request->header('X-Forwarded-For'),
            'headers_host' => $request->header('host'),
            'remote_ip' => $request->ip(),
        ]);

        if (!$hasValidSignature && $expiresAt) {
            Log::channel('single')->error('[DEBUG-SIGNATURE] FIRMA INVALIDA', [
                'expires_unix' => (int)$expiresAt,
                'now_unix' => now()->timestamp,
                'difference_seconds' => (int)$expiresAt - now()->timestamp,
                'url_generated_datetime' => date('Y-m-d H:i:s', (int)$expiresAt),
                'scheme_mismatch' => $request->getScheme() !== parse_url(config('app.url'), PHP_URL_SCHEME),
                'request_scheme' => $request->getScheme(),
                'app_url_scheme' => parse_url(config('app.url'), PHP_URL_SCHEME),
            ]);
        }

        try {
            $response = $next($request);
        } catch (\Exception $e) {
            Log::channel('single')->error('[DEBUG-SIGNATURE] EXCEPTION', [
                'exception_class' => get_class($e),
                'exception_message' => $e->getMessage(),
            ]);
            throw $e;
        }

        Log::channel('single')->info('[DEBUG-SIGNATURE] RESPUESTA', [
            'status_code' => $response->getStatusCode(),
        ]);

        return $response;
    }
}
