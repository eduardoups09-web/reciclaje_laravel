<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    /** Muestra el formulario de login. */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    /** Procesa el login: acepta email O username, contra las contraseñas bcrypt existentes. */
    public function login(Request $request)
    {
        $data = $request->validate([
            'login'    => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [], ['login' => 'usuario o correo']);

        // Busca por email o username, solo usuarios no eliminados.
        $user = User::activos()
            ->where(function ($q) use ($data) {
                $q->where('email', $data['login'])
                  ->orWhere('username', $data['login']);
            })
            ->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            return back()
                ->withInput($request->only('login'))
                ->withErrors(['login' => 'Credenciales incorrectas.']);
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    /** Cierra la sesión. */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
