<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Tenant;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        // Identify tenant from subdomain (example: tenant1.podosoft.test)
        $host = request()->getHost();
        $slug = explode('.', $host)[0];
        $tenant = Tenant::where('slug', $slug)->first();

        if (!$tenant) {
            return response()->view('errors.404', ['message' => 'Consultorio no encontrado.'], 404);
        }

        return view('auth.login', compact('tenant'));
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('dashboard');
        }

        return back()->withErrors([
            'email' => 'Las credenciales proporcionadas no coinciden con nuestros registros.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
