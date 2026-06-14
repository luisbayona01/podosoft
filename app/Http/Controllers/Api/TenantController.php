<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TenantController extends Controller
{
    /**
     * Resolve Tenant ID based on a slug or identifier.
     * Used by external AI services to determine the correct tenant for registration.
     */
    public function resolve(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'slug' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'El slug es requerido para identificar la compañía',
                'errors' => $validator->errors()
            ], 422);
        }

        $tenant = Tenant::where('slug', $request->slug)->where('activo', true)->first();

        if (!$tenant) {
            return response()->json([
                'status' => 'error',
                'message' => 'Compañía no encontrada o inactiva'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'tenant_id' => $tenant->id,
                'nombre' => $tenant->nombre
            ]
        ]);
    }
}
