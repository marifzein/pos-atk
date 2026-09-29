<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Support\Facades\Cookie;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Ambil role user yang baru login
        $role = strtolower(auth()->user()->role);

        // Tentukan route tujuan berdasarkan role
        $targetRoute = match ($role) {
            'staff barang' => 'pesanan-barang.index',
            'staff jasa'   => 'pesanan-jasa.index',
            'kasir'        => 'kasir.index',
            'supervisor'   => 'purchasing.index',
            default        => 'dashboard',
        };

        return redirect()->intended(route($targetRoute, absolute: false));
        // return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        // 2. Paksa hapus cookie laravel_session dari browser
        $cookie = Cookie::forget(config('session.cookie'));

        // 3. Redirect ke login dengan membawa instruksi hapus cookie
        return redirect()->route('login')->withCookie($cookie);

        // return redirect('/');
    }
}
