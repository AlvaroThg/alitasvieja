<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => 'Las credenciales no son correctas.',
            ]);
        }

        $user = Auth::user();

        if (!$user->is_active) {
            Auth::logout();
            return back()->withErrors(['email' => 'Tu cuenta está inactiva.']);
        }

        $request->session()->regenerate();

        // El owner va al dashboard global; el resto al POS de su sucursal
        return $user->isOwner()
            ? redirect()->route('admin.dashboard')
            : redirect()->route('pos.index');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    // Solo para el owner: cambiar de sucursal activa
    public function switchBranch(Request $request)
    {
        $request->validate(['branch_id' => ['required', 'exists:branches,id']]);
        session(['active_branch_id' => $request->branch_id]);
        return back();
    }
}
