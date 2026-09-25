<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use App\Services\TenantManager;

class AuthController extends Controller
{
    public function showLogin()
    {
        // Jika sudah login, arahkan ke portal yang sesuai
        if (Auth::check()) {
            if (TenantManager::isPlatformAdmin()) {
                return redirect()->route('platform.dashboard');
            }
            return redirect()->route('superadmin.dashboard');
        }
        return view('auth.login');
    }

    public function prosesLogin(Request $request)
    {
        $request->validate([
            'username' => ['required'],
            'password' => ['required'],
        ]);

        $loginInput = trim($request->input('username'));
        $password = $request->input('password');

        // Dukung login via username maupun email
        $isEmail = filter_var($loginInput, FILTER_VALIDATE_EMAIL);
        $attempts = [];

        if ($isEmail && Schema::hasColumn('users', 'email')) {
            $attempts[] = ['email' => $loginInput, 'password' => $password];
        }
        $attempts[] = ['username' => $loginInput, 'password' => $password];
        if (!$isEmail && Schema::hasColumn('users', 'email')) {
            $attempts[] = ['email' => $loginInput, 'password' => $password];
        }

        $berhasilLogin = false;
        foreach ($attempts as $credentials) {
            if (Auth::attempt($credentials, $request->boolean('remember'))) {
                $berhasilLogin = true;
                break;
            }
        }

        if ($berhasilLogin) {
            $request->session()->regenerate();
            
            // Cek status user, kalau nonaktif langsung batalkan
            if (Auth::user()->status === 'nonaktif') {
                Auth::logout();
                return back()->with('error', 'Akun Anda dinonaktifkan. Silakan hubungi Superadmin Platform.');
            }

            // [PEMISAHAN PORTAL]:
            // Superadmin Utama -> Masuk ke Master SaaS Platform Control Panel
            // Admin Toko / Staf -> Masuk ke POS Operasional Toko
            if (TenantManager::isPlatformAdmin()) {
                return redirect()->route('platform.dashboard')
                    ->with('success', 'Selamat datang di Pusat Kendali Master Platform VxPOS!');
            }

            return redirect()->route('superadmin.dashboard')
                ->with('success', 'Selamat datang di POS Dashboard Toko.');
        }

        return back()->with('error', 'Kredensial login tidak cocok. Pastikan username dan password benar.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}