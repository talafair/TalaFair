<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('pages.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'login'    => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $field = filter_var($credentials['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        if (Auth::attempt(
            [$field => $credentials['login'], 'password' => $credentials['password']],
            $request->boolean('remember')
        )) {
            $request->session()->regenerate();

            $user = Auth::user();
            AuditLog::create([
                'user_id' => $user->id,
                'actor_unique_id' => $user->unique_id,
                'action' => 'login',
                'auditable_type' => get_class($user),
                'auditable_id' => $user->id,
                'ip_address' => $request->ip(),
                'duration_ms' => $request->attributes->get('started_at')
                    ? (int) round((microtime(true) - $request->attributes->get('started_at')) * 1000)
                    : null,
            ]);

            return redirect()->intended(route('home'));
        }

        return back()
            ->withErrors(['login' => 'The provided credentials do not match our records.'])
            ->onlyInput('login');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}