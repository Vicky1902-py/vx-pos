<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        // Jika sudah login, jangan boleh buka halaman login lagi, arahkan ke pintu utama
        if (Auth::check()) {
            return redirect('/superadmin/dashboard');
        }
        return view('auth.login');
    }

    public function prosesLogin(Request $request)
    {
        $kredensial = $request->validate([
            'username' => ['required'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($kredensial)) {
            $request->session()->regenerate();
            
            // Cek status user, kalau nonaktif langsung tendang
            if (Auth::user()->status == 'nonaktif') {
                Auth::logout();
                return back()->with('error', 'Akun Anda dinonaktifkan. Hubungi Super Admin.');
            }

            // [PERBAIKAN] Semua role sekarang diarahkan ke satu pintu utama
            // Keamanan dan visibilitas menu akan diurus oleh sistem ACL
            return redirect('/superadmin/dashboard');
        }

        return back()->with('error', 'Username atau password salah!');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}