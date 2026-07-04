<?php

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== TESTING SIGNED URL FLOW ===\n\n";

$tenantSlug = 'clinica-podosoft-central';
$documento = '1024482240';
$phone = '573127938280';

echo "Config:\n";
echo "  APP_URL: " . config('app.url') . "\n";
echo "  APP_KEY: " . config('app.key') . "\n";
echo "  Now: " . now()->toIso8601String() . "\n";
echo "  Now Unix: " . now()->timestamp . "\n\n";

$expiresAt = now()->addDays(7);
echo "Expires (7 days from now): " . $expiresAt->toIso8601String() . "\n";
echo "Expires Unix: " . $expiresAt->timestamp . "\n\n";

$params = [
    'tenant' => $tenantSlug,
    'documento' => $documento,
    'phone' => $phone,
];

echo "Generating signed URL with params: " . json_encode($params) . "\n\n";

$signedUrl = URL::temporarySignedRoute(
    'public.patient.register',
    $expiresAt,
    $params
);

echo "Generated URL:\n  $signedUrl\n\n";

$parsed = parse_url($signedUrl);
parse_str($parsed['query'] ?? '', $queryParams);

echo "Parsed URL:\n";
echo "  Scheme: " . ($parsed['scheme'] ?? 'N/A') . "\n";
echo "  Host: " . ($parsed['host'] ?? 'N/A') . "\n";
echo "  Path: " . ($parsed['path'] ?? 'N/A') . "\n";
echo "  Query Params:\n";
foreach ($queryParams as $key => $value) {
    echo "    $key: $value\n";
}

echo "\n";

echo "=== Simulating URL access (hasValidSignature check) ===\n\n";

$testUrl = $signedUrl;
echo "Test URL: $testUrl\n\n";

$request = Request::create($testUrl, 'GET');

echo "Request details:\n";
echo "  fullUrl(): " . $request->fullUrl() . "\n";
echo "  url(): " . $request->url() . "\n";
echo "  getScheme(): " . $request->getScheme() . "\n";
echo "  getHost(): " . $request->getHost() . "\n";
echo "  path(): " . $request->path() . "\n";
echo "  isSecure(): " . ($request->isSecure() ? 'true' : 'false') . "\n";
echo "  queryString(): " . $request->queryString() . "\n";
echo "  hasValidSignature(): " . ($request->hasValidSignature() ? 'true' : 'false') . "\n\n";

echo "Comparing scheme/host/path:\n";
echo "  APP_URL scheme: " . parse_url(config('app.url'), PHP_URL_SCHEME) . "\n";
echo "  Request scheme: " . $request->getScheme() . "\n";
echo "  Match: " . (parse_url(config('app.url'), PHP_URL_SCHEME) === $request->getScheme() ? 'YES' : 'NO') . "\n\n";

echo "  APP_URL host: " . parse_url(config('app.url'), PHP_URL_HOST) . "\n";
echo "  Request host: " . $request->getHost() . "\n";
echo "  Match: " . (parse_url(config('app.url'), PHP_URL_HOST) === $request->getHost() ? 'YES' : 'NO') . "\n\n";

echo "  APP_URL path: " . parse_url(config('app.url'), PHP_URL_PATH) . "\n";
echo "  Request path: " . '/' . $request->path() . "\n";
echo "  Match: " . (('/' . $request->path()) === parse_url(config('app.url'), PHP_URL_PATH) ? 'YES' : 'NO') . "\n\n";

echo "=== Test Complete ===\n";
