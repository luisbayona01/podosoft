<?php

namespace App\Http\Controllers;

use App\Services\ShortLinkService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

class ShortLinkController extends Controller
{
    public function __construct(
        protected ShortLinkService $shortLinkService
    ) {}

    public function redirect(string $code): RedirectResponse
    {
        $shortLink = $this->shortLinkService->findValidByCode($code);

        if (!$shortLink) {
            Log::channel('single')->error('[DEBUG-SIGNATURE] ShortLinkController::redirect - NO ENCONTRADO', [
                'code' => $code,
            ]);
            abort(404, 'Enlace no encontrado o expirado.');
        }

        $originalUrl = $shortLink->original_url;

        Log::channel('single')->info('[DEBUG-SIGNATURE] ShortLinkController::redirect - ANTES DEL REDIRECT', [
            'code' => $code,
            'original_url' => $originalUrl,
            'original_url_length' => strlen($originalUrl),
            'url_hash' => md5($originalUrl),
        ]);

        return redirect($originalUrl, 302);
    }
}
