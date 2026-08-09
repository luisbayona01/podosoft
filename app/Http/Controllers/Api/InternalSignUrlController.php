<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Services\ShortLinkService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;

/**
 * Internal endpoint used by the Python AI service (podosoft-ai) to mint
 * signed URLs the agent can send to the patient via WhatsApp.
 *
 * Auth: requires the X-Service-Token header matching config('services.laravel.service_token')
 *       (the same token used by Python when calling Laravel's public APIs).
 *
 * Request:
 *   POST /api/v1/internal/sign-url
 *   Headers: X-Service-Token: <secret>
 *   Body:
 *     {
 *       "route": "public.appointment" | "public.patient.register" | "public.services",
 *       "tenant_slug": "clinica-x",
 *       "params": {"phone": "...", "documento": "..."},   // optional
 *       "short": true                                      // optional, default true
 *     }
 *
 * Response:
 *   200 {"url": "https://podosoft.test/r/abc123"}
 *   404 if tenant_slug doesn't exist
 *   422 on validation error
 */
class InternalSignUrlController extends Controller
{
    private const ALLOWED_ROUTES = [
        'public.appointment',
        'public.patient.register',
        'public.services',
    ];

    public function __construct(private readonly ShortLinkService $shortLinks)
    {
    }

    public function __invoke(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'route'       => 'required|string|in:' . implode(',', self::ALLOWED_ROUTES),
                'tenant_slug' => 'required|string|max:64',
                'params'      => 'sometimes|array',
                'short'       => 'sometimes|boolean',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'code' => 'validation_failed',
                'message' => 'Invalid sign-url request',
                'errors' => $e->errors(),
            ], 422);
        }

        $tenant = Tenant::where('slug', $data['tenant_slug'])->first();
        if (!$tenant) {
            return response()->json([
                'status' => 'error',
                'code' => 'tenant_not_found',
                'message' => "Tenant '{$data['tenant_slug']}' not found",
            ], 404);
        }

        $params = array_merge(
            ['tenant' => $tenant->slug],
            $data['params'] ?? []
        );

        $signedUrl = URL::temporarySignedRoute(
            $data['route'],
            now()->addDays(7),
            $params
        );

        $url = ($data['short'] ?? true)
            ? $this->shortLinks->getUrl($signedUrl, $tenant->id)
            : $signedUrl;

        Log::info('[InternalSignUrl] Generated URL', [
            'tenant' => $tenant->slug,
            'route' => $data['route'],
            'short' => $url !== $signedUrl,
        ]);

        return response()->json(['url' => $url]);
    }
}
