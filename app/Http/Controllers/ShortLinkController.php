<?php

namespace App\Http\Controllers;

use App\Services\ShortLinkService;
use Illuminate\Http\RedirectResponse;

class ShortLinkController extends Controller
{
    public function __construct(
        protected ShortLinkService $shortLinkService
    ) {}

    public function redirect(string $code): RedirectResponse
    {
        $shortLink = $this->shortLinkService->findValidByCode($code);

        if (!$shortLink) {
            abort(404, 'Enlace no encontrado o expirado.');
        }

        return redirect($shortLink->original_url, 302);
    }
}