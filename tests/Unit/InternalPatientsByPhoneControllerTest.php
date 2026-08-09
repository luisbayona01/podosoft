<?php

namespace Tests\Unit;

use App\Http\Controllers\Api\InternalPatientsByPhoneController;
use Illuminate\Http\Request;
use Tests\TestCase;

class InternalPatientsByPhoneControllerTest extends TestCase
{
    public function test_returns_422_for_missing_phone(): void
    {
        $controller = new InternalPatientsByPhoneController();
        $request = Request::create('/api/v1/internal/patients/by-phone?tenant_id=1', 'GET');

        $response = $controller($request);
        $this->assertSame(422, $response->getStatusCode());
    }

    public function test_returns_422_for_missing_tenant_id(): void
    {
        $controller = new InternalPatientsByPhoneController();
        $request = Request::create('/api/v1/internal/patients/by-phone?phone=573001234567', 'GET');

        $response = $controller($request);
        $this->assertSame(422, $response->getStatusCode());
    }

    public function test_returns_422_for_phone_too_short(): void
    {
        $controller = new InternalPatientsByPhoneController();
        $request = Request::create('/api/v1/internal/patients/by-phone?phone=123&tenant_id=1', 'GET');

        $response = $controller($request);
        $this->assertSame(422, $response->getStatusCode());
    }

    public function test_returns_422_for_tenant_id_zero(): void
    {
        $controller = new InternalPatientsByPhoneController();
        $request = Request::create('/api/v1/internal/patients/by-phone?phone=573001234567&tenant_id=0', 'GET');

        $response = $controller($request);
        $this->assertSame(422, $response->getStatusCode());
    }
}
